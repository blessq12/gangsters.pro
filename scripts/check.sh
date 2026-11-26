#!/usr/bin/env bash
# Единый прогон качества: frontend + backend.
# Использование:
#   ./scripts/check.sh          # только проверки
#   ./scripts/check.sh --fix    # автопочинка (prettier/pint) + проверки
#   npm run check
#   npm run check:fix

set -euo pipefail

ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$ROOT"

MODE="check"
if [[ "${1:-}" == "--fix" ]]; then
    MODE="fix"
fi

PASS=()
FAIL=()
SKIP=()

red() { printf '\033[31m%s\033[0m\n' "$*"; }
green() { printf '\033[32m%s\033[0m\n' "$*"; }
yellow() { printf '\033[33m%s\033[0m\n' "$*"; }
bold() { printf '\033[1m%s\033[0m\n' "$*"; }

have_cmd() {
    command -v "$1" >/dev/null 2>&1
}

run_step() {
    local name="$1"
    shift
    bold ""
    bold "==> $name"
    if "$@"; then
        PASS+=("$name")
        green "OK: $name"
        return 0
    fi
    FAIL+=("$name")
    red "FAIL: $name"
    return 1
}

print_summary() {
    bold ""
    bold "======== ИТОГ ========"
    if ((${#PASS[@]} > 0)); then
        green "Прошли (${#PASS[@]}):"
        for item in "${PASS[@]}"; do
            green "  - $item"
        done
    fi
    if ((${#SKIP[@]} > 0)); then
        yellow "Пропущены (${#SKIP[@]}):"
        for item in "${SKIP[@]}"; do
            yellow "  - $item"
        done
    fi
    if ((${#FAIL[@]} > 0)); then
        red "Упали (${#FAIL[@]}):"
        for item in "${FAIL[@]}"; do
            red "  - $item"
        done
        bold ""
        red "Качество: FAIL"
        return 1
    fi
    bold ""
    green "Качество: PASS"
    return 0
}

# --- prerequisites ---
if ! have_cmd npm; then
    red "npm не найден"
    exit 1
fi
if [[ ! -f "$ROOT/vendor/bin/pint" || ! -f "$ROOT/vendor/bin/phpstan" ]]; then
    red "Нет vendor/bin/pint или vendor/bin/phpstan — сначала: composer install"
    exit 1
fi

bold "Режим: $MODE"
bold "Корень: $ROOT"

# --- auto-fix (optional) ---
if [[ "$MODE" == "fix" ]]; then
    run_step "Prettier --write" npm run format || {
        print_summary
        exit 1
    }
    run_step "Laravel Pint" ./vendor/bin/pint || {
        print_summary
        exit 1
    }
fi

# --- checks (always) ---
run_step "knip" npm run knip || {
    print_summary
    exit 1
}

run_step "ESLint" npm run lint || {
    print_summary
    exit 1
}

run_step "Prettier --check" npm run format:check || {
    print_summary
    exit 1
}

run_step "Laravel Pint --test" ./vendor/bin/pint --test || {
    print_summary
    exit 1
}

run_step "PHPStan" ./vendor/bin/phpstan analyse --memory-limit=1G || {
    print_summary
    exit 1
}

print_summary
exit $?
