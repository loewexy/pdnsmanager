const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        var res = await req({
            url: '/remote/ip',
            method: 'get'
        });

        assert.equal(res.status, 200);
        assert.equal(res.data, { ip: '127.0.0.1' }, 'No proxy header should return tcp client ip.');

        var res = await req({
            url: '/remote/ip',
            method: 'get',
            headers: {
                'X-Forwarded-For': '1.2.3.4, 127.0.0.1'
            }
        });

        assert.equal(res.status, 200);
        assert.equal(res.data, { ip: '1.2.3.4' }, 'X-Forwarded-For Test 1');

        var res = await req({
            url: '/remote/ip',
            method: 'get',
            headers: {
                'X-Forwarded-For': '4.3.2.1, 1.2.3.4, 127.0.0.1'
            }
        });

        assert.equal(res.status, 200);
        assert.equal(res.data, { ip: '1.2.3.4' }, 'X-Forwarded-For Test 2');

        var res = await req({
            url: '/remote/ip',
            method: 'get',
            headers: {
                'X-Forwarded-For': '4.3.2.1, 1.2.3.4'
            }
        });

        assert.equal(res.status, 200);
        assert.equal(res.data, { ip: '127.0.0.1' }, 'X-Forwarded-For Test 3');
    });
});