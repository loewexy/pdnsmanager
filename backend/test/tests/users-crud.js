const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test missing fields
        var res = await req({
            url: '/users',
            method: 'post',
            data: {
                name: 'newadmin',
                type: 'admin'
            }
        });
        assert.equal(res.status, 422, 'Missing fields should trigger error.');

        //Test invalid type
        var res = await req({
            url: '/users',
            method: 'post',
            data: {
                name: 'newadmin',
                type: 'foo',
                password: 'foo'
            }
        });
        assert.equal(res.status, 400, 'Invalid type should trigger error.');

        //Test duplicate user
        var res = await req({
            url: '/users',
            method: 'post',
            data: {
                name: 'admin',
                type: 'admin',
                password: 'foo'
            }
        });
        assert.equal(res.status, 409, 'Duplicate user should trigger error.');

        //Test user creation
        var res = await req({
            url: '/users',
            method: 'post',
            data: {
                name: 'newadmin',
                type: 'admin',
                password: 'newadmin'
            }
        });
        assert.equal(res.status, 201, 'User creation should succeed.');
        assert.equal(res.data, { id: 4, name: 'newadmin', type: 'admin' }, 'Add user data fail.');

        //Test if new user can log in
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'newadmin',
                password: 'newadmin'
            }
        });
        assert.equal(res.status, 201, 'Login with new user should succeed.');

        //Test user get
        var res = await req({
            url: '/users/4',
            method: 'get'
        });
        assert.equal(res.status, 200, 'New user should be found.');
        assert.equal(res.data, { id: 4, name: 'newadmin', type: 'admin', native: true }, 'New user data fail.');

        //Test user change without data
        var res = await req({
            url: '/users/4',
            method: 'put',
            data: { dummy: 'foo' }
        });
        assert.equal(res.status, 204, 'Update without field should succeed.');

        //Test user get
        var res = await req({
            url: '/users/4',
            method: 'get'
        });
        assert.equal(res.status, 200, 'New user should be found after update.');
        assert.equal(res.data, { id: 4, name: 'newadmin', type: 'admin', native: true }, 'New user should not change by noop update.');

        //Test user update
        var res = await req({
            url: '/users/4',
            method: 'put',
            data: {
                name: 'foo',
                password: 'bar',
                type: 'user'
            }
        });
        assert.equal(res.status, 204, 'Update should succeed.');

        //Test if updated user can log in
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'foo',
                password: 'bar'
            }
        });
        assert.equal(res.status, 201, 'Login with updated user should succeed.');

        //Test user get
        var res = await req({
            url: '/users/4',
            method: 'get'
        });
        assert.equal(res.status, 200, 'New user should be found after second update.');
        assert.equal(res.data, { id: 4, name: 'foo', type: 'user', native: true }, 'New user should change by update.');

        //Test user update conflict
        var res = await req({
            url: '/users/4',
            method: 'put',
            data: {
                name: 'admin'
            }
        });
        assert.equal(res.status, 409, 'Update with existent name should fail.');

        //Test user delete for not existing user
        var res = await req({
            url: '/users/100',
            method: 'delete'
        });
        assert.equal(res.status, 404, 'Deletion of not existens user should fail.');

        //Test user delete
        var res = await req({
            url: '/users/4',
            method: 'delete'
        });
        assert.equal(res.status, 204, 'Deletion of user should succeed.');

        var res = await req({
            url: '/users/4',
            method: 'get'
        });
        assert.equal(res.status, 404, 'New user should not be found after deletion.');

        // Test me alias get
        var res = await req({
            url: '/users/me',
            method: 'get'
        });
        assert.equal(res.status, 200, 'Admin should be able to use /me.');
        assert.equal(res.data, { id: 1, name: 'admin', type: 'admin', native: true }, 'Admin /me data fail.');

        // Test me alias update
        var res = await req({
            url: '/users/me',
            method: 'put',
            data: {
                password: 'abc'
            }
        });
        assert.equal(res.status, 204, 'Admin should be able to update /me.');

        //Test if updated user can log in
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'admin',
                password: 'abc'
            }
        });
        assert.equal(res.status, 201, 'Login with updated admin should succeed.');
    });

    await test('user', async function (assert, req) {
        // Test me alias get
        var res = await req({
            url: '/users/me',
            method: 'get'
        });
        assert.equal(res.status, 200, 'User should be able to use /me.');
        assert.equal(res.data, { id: 2, name: 'user', type: 'user', native: true }, 'User /me data fail.');

        // Test me alias update
        var res = await req({
            url: '/users/me',
            method: 'put',
            data: {
                password: 'abc'
            }
        });
        assert.equal(res.status, 204, 'User should be able to update /me.');

        //Test if updated user can log in
        var res = await req({
            url: '/sessions',
            method: 'post',
            data: {
                username: 'user',
                password: 'abc'
            }
        });
        assert.equal(res.status, 201, 'Login with updated user should succeed.');
    });
});