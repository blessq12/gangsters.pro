import http from "k6/http";
import { check, sleep } from "k6";

const baseUrl = __ENV.BASE_URL || "http://127.0.0.1:8000";

export const options = {
    scenarios: {
        catalogRead: {
            executor: "constant-vus",
            vus: 10,
            duration: "60s",
            exec: "catalogRead",
        },
        systemRead: {
            executor: "constant-vus",
            vus: 5,
            duration: "60s",
            exec: "systemRead",
        },
        yandexAuth: {
            executor: "constant-vus",
            vus: 3,
            duration: "60s",
            exec: "yandexAuth",
        },
    },
    thresholds: {
        http_req_failed: ["rate<0.02"],
        http_req_duration: ["p(95)<800", "p(99)<1500"],
    },
};

export function catalogRead() {
    const res = http.get(`${baseUrl}/api/catalog`);
    check(res, {
        "catalog status is 200": (r) => r.status === 200,
    });
    sleep(1);
}

export function systemRead() {
    const res = http.get(`${baseUrl}/api/system/company`);
    check(res, {
        "system company status is 200": (r) => r.status === 200,
    });
    sleep(1);
}

export function yandexAuth() {
    const payload = JSON.stringify({
        client_id: "invalid",
        client_secret: "invalid",
    });

    const res = http.post(`${baseUrl}/api/yandex-food/security/oauth/token`, payload, {
        headers: { "Content-Type": "application/json" },
    });

    check(res, {
        "yandex auth endpoint responds": (r) => r.status >= 200 && r.status < 500,
    });
    sleep(1);
}
