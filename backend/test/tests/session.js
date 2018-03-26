const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Try to login with invalid username and password
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'foo',
                password: 'bar'
            }
        });

        assert.equal(res.status, 403, 'Status not valid');

        //Try to login with invalid username
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'foo',
                password: 'admin'
            }
        });

        assert.equal(res.status, 403, 'Status not valid');

        //Try to login with invalid password
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'admin',
                password: 'foo'
            }
        });

        assert.equal(res.status, 403, 'Status not valid');

        //Try to login with missing field
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                password: 'admin'
            }
        });

        assert.equal(res.status, 422, 'Status not valid');

        //Try to login with invalid username and password
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'foo/admin',
                password: 'admin'
            }
        });

        assert.equal(res.status, 201, 'Status not valid');
    });
});