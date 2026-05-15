import { describe, it } from "node:test";
import assert from "node:assert/strict";
import { resolveDeliveryDockMapMode } from "../../resources/js/utils/maps/resolveDeliveryDockMapMode.js";

describe("resolveDeliveryDockMapMode", () => {
    it("returns zone-sdk when api key is present", () => {
        assert.equal(
            resolveDeliveryDockMapMode({
                hasApiKey: true,
                hasAddress: true,
                isLoading: false,
            }),
            "zone-sdk",
        );
    });

    it("returns zone-sdk without address when api key is present", () => {
        assert.equal(
            resolveDeliveryDockMapMode({
                hasApiKey: true,
                hasAddress: false,
                isLoading: false,
            }),
            "zone-sdk",
        );
    });

    it("returns widget when no api key but address exists", () => {
        assert.equal(
            resolveDeliveryDockMapMode({
                hasApiKey: false,
                hasAddress: true,
                isLoading: false,
            }),
            "widget",
        );
    });

    it("returns loading when no key, no address, loading", () => {
        assert.equal(
            resolveDeliveryDockMapMode({
                hasApiKey: false,
                hasAddress: false,
                isLoading: true,
            }),
            "loading",
        );
    });

    it("returns fallback when no key, no address, not loading", () => {
        assert.equal(
            resolveDeliveryDockMapMode({
                hasApiKey: false,
                hasAddress: false,
                isLoading: false,
            }),
            "fallback",
        );
    });
});
