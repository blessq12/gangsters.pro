import assert from "node:assert/strict";
import { describe, it } from "node:test";
import {
    administratorCommands,
    shortStage,
    ticketHint,
} from "./orderStage.js";

describe("administratorCommands", () => {
    it("новый заказ: подтвердить и отменить, без готовки", () => {
        const ids = administratorCommands({ status: "new", delivery: { method: "courier" } })
            .map((command) => command.id);
        assert.deepEqual(ids, ["confirm", "cancel"]);
    });

    it("подтверждённый: только отмена, без переназначения филиала", () => {
        const ids = administratorCommands({
            status: "confirmed",
            delivery: { method: "pickup" },
        }).map((command) => command.id);
        assert.deepEqual(ids, ["cancel"]);
    });

    it("готовится: кухня сама завершает тикет, администратор может только отменить", () => {
        const ids = administratorCommands({
            status: "preparing",
            delivery: { method: "courier" },
        }).map((command) => command.id);
        assert.deepEqual(ids, ["cancel"]);
    });

    it("готов самовывоз: выдать, не класть в маршрутный лист", () => {
        const ids = administratorCommands({
            status: "ready",
            delivery: { method: "pickup" },
        }).map((command) => command.id);
        assert.deepEqual(ids, ["pickup", "cancel"]);
    });

    it("готов курьером: в маршрутный лист, не выдавать как самовывоз", () => {
        const ids = administratorCommands({
            status: "ready",
            delivery: { method: "courier" },
        }).map((command) => command.id);
        assert.deepEqual(ids, ["toRoute", "cancel"]);
    });

    it("в пути и терминальные этапы без команд", () => {
        for (const status of ["in_transit", "delivered", "written_off", "cancelled"]) {
            assert.deepEqual(administratorCommands({ status, delivery: { method: "courier" } }), []);
        }
    });

    it("в листе ещё можно отменить, но не подтвердить", () => {
        const ids = administratorCommands({
            status: "assigned_to_route",
            delivery: { method: "courier" },
        }).map((command) => command.id);
        assert.deepEqual(ids, ["cancel"]);
    });
});

describe("shortStage", () => {
    it("говорит следующим действием, а не кодом статуса", () => {
        assert.equal(shortStage({ status: "ready", delivery: { method: "pickup" } }), "Выдать самовывоз");
        assert.equal(shortStage({ status: "in_transit", delivery: { method: "courier" } }), "Закрыть остановку");
        assert.equal(ticketHint("queued").includes("старт"), true);
    });
});
