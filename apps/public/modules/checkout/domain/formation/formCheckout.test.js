import assert from "node:assert/strict";
import { readFileSync } from "node:fs";
import { describe, it } from "node:test";
import { formCheckout } from "./formCheckout.js";

const fixture = JSON.parse(
    readFileSync(
        new URL("../../../../../../tests/fixtures/order-formation/cases.json", import.meta.url),
        "utf8",
    ),
);

describe("formCheckout ↔ OrderFormation (общие эталоны)", () => {
    for (const testCase of fixture.cases) {
        it(testCase.name, () => {
            const result = formCheckout(fixture.context, testCase.intent, new Date(testCase.now));

            assert.deepEqual(
                result.cart.lines.map(
                    (line) => `${line.payload.kind}:${line.product_id}x${line.quantity}`,
                ),
                testCase.expected.lines,
            );
            assert.equal(result.totals.items_total, testCase.expected.items_total);
            assert.equal(result.totals.delivery_fee, testCase.expected.delivery_fee);
            assert.equal(result.totals.grand_total, testCase.expected.grand_total);
            assert.equal(result.delivery.in_zone, testCase.expected.in_zone);
        });
    }

    it("курьер без координат: зона неизвестна, доставка не считается", () => {
        const result = formCheckout(fixture.context, {
            lines: [{ product_id: 50, kind: "product", quantity: 1 }],
            delivery_method: "courier",
            address: { street: "Ленина", house: "1" },
        });

        assert.equal(result.delivery.in_zone, null);
        assert.equal(result.totals.delivery_fee, 0);
    });

    it("товар не из меню филиала выпадает из корзины", () => {
        const result = formCheckout(fixture.context, {
            lines: [
                { product_id: 50, kind: "product", quantity: 1 },
                { product_id: 999, kind: "product", quantity: 1 },
            ],
            delivery_method: "pickup",
        });

        assert.deepEqual(result.unavailable_product_ids, [999]);
        assert.equal(result.totals.items_total, 25000);
    });

    it("пустая корзина → null", () => {
        assert.equal(formCheckout(fixture.context, { lines: [], delivery_method: "pickup" }), null);
    });
});
