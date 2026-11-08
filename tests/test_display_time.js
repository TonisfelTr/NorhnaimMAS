'use strict';
const assert = require('node:assert/strict');
const fs = require('node:fs');
const vm = require('node:vm');
const path = require('node:path');
const script = fs.readFileSync(path.join(__dirname, '../payload/public/js/clinic-appointment-time.js'), 'utf8');
const context = {
    window: {}, document: {readyState: 'loading', addEventListener() {}},
    Intl, Date, Map, Set, Number, Array, String, localStorage: {getItem() { return null; }},
};
vm.createContext(context);
vm.runInContext(script, context);
const api = context.window.ClinicAppointmentTime;
const cases = [
    ['Moscow', '2026-11-10T12:50', 'Europe/Moscow', '2026-11-10T09:50:00.000Z'],
    ['Tomsk', '2026-11-10T12:50', 'Asia/Tomsk', '2026-11-10T05:50:00.000Z'],
    ['Nepal fractional offset', '2026-11-10T12:50', 'Asia/Kathmandu', '2026-11-10T07:05:00.000Z'],
    ['New York valid after gap', '2026-03-08T03:30', 'America/New_York', '2026-03-08T07:30:00.000Z'],
    ['New York missing local hour', '2026-03-08T02:30', 'America/New_York', null],
    ['New York repeated local hour', '2026-11-01T01:30', 'America/New_York', null],
    ['Berlin repeated local hour', '2026-10-25T02:30', 'Europe/Berlin', null],
    ['Invalid calendar date', '2026-02-30T12:50', 'Europe/Moscow', null],
    ['Second precision rejected', '2026-11-10T12:50:03', 'Europe/Moscow', null],
    ['UTC midnight', '2026-11-10T00:00', 'UTC', '2026-11-10T00:00:00.000Z'],
];
for (const [label, civil, zone, expected] of cases) {
    const got = api.resolveLocal(civil, zone);
    assert.equal(got === null ? null : new Date(got).toISOString(), expected, label);
}
const carrier = api.civilNow('Europe/Moscow', Date.parse('2026-11-07T21:50:00Z'));
assert.equal(carrier.getUTCFullYear(), 2026);
assert.equal(carrier.getUTCMonth(), 10);
assert.equal(carrier.getUTCDate(), 8);
assert.equal(carrier.getUTCHours(), 0);
assert.equal(carrier.getUTCMinutes(), 50);
assert.equal(carrier.getUTCDay(), 0);
assert.equal(api.validZone('Bogus/Timezone'), false);
console.log(`PASS: ${cases.length} local-time cases, clinic midnight/day boundary and invalid zone (browser process TZ=${process.env.TZ || 'default'}).`);
