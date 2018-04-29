const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        var res = await req({
            url: '/remote/servertime',
            method: 'get'
        });

        const curTime = Math.floor(new Date() / 1000);

        assert.equal(res.status, 200);
        assert.true(Math.abs(curTime - res.data.time) < 2, 'Returned time is not within tolerance!');
    });
});