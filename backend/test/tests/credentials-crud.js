const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Test missing field
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test'
            }
        });

        assert.equal(res.status, 422);

        //Test invalid type
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test',
                type: 'foo'
            }
        });

        assert.equal(res.status, 400);

        //Test missing key
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test',
                type: 'key'
            }
        });

        assert.equal(res.status, 422);

        //Test missing password
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test',
                type: 'password'
            }
        });

        assert.equal(res.status, 422);

        //Test invalid key
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test',
                type: 'key',
                key: 'foo'
            }
        });

        assert.equal(res.status, 400);

        //Test invalid record
        var res = await req({
            url: '/records/100/credentials',
            method: 'post',
            data: {
                description: 'Test',
                type: 'password',
                password: 'foo'
            }
        });

        assert.equal(res.status, 404, 'Not existent record should trigger error.');

        //Add key (key is intensionally very short but valid) and get it
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test Key',
                type: 'key',
                key: '-----BEGIN PUBLIC KEY-----\nMDwwDQYJKoZIhvcNAQEBBQADKwAwKAIhAMOLSxmtlYxSkEKep11gjq200PTKVUaA\nyalonAKxw3XnAgMBAAE=\n-----END PUBLIC KEY-----'
            }
        });

        assert.equal(res.status, 201, 'Adding key should succeed.');
        assert.equal(res.data, {
            id: 4,
            description: 'Test Key',
            type: 'key',
            key: '-----BEGIN PUBLIC KEY-----\nMDwwDQYJKoZIhvcNAQEBBQADKwAwKAIhAMOLSxmtlYxSkEKep11gjq200PTKVUaA\nyalonAKxw3XnAgMBAAE=\n-----END PUBLIC KEY-----'
        }, 'Adding credential data fail.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Added key should be found.');
        assert.equal(res.data, {
            id: 4,
            description: 'Test Key',
            type: 'key',
            key: '-----BEGIN PUBLIC KEY-----\nMDwwDQYJKoZIhvcNAQEBBQADKwAwKAIhAMOLSxmtlYxSkEKep11gjq200PTKVUaA\nyalonAKxw3XnAgMBAAE=\n-----END PUBLIC KEY-----'
        }, 'Added key does not match.');

        //Add password and get it
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test Password',
                type: 'password',
                password: 'foo'
            }
        });

        assert.equal(res.status, 201, 'Adding password should succeed.');
        assert.equal(res.data, {
            id: 5,
            description: 'Test Password',
            type: 'password',
        }, 'Adding credential data fail.');

        var res = await req({
            url: '/records/1/credentials/5',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Added key should be found.');
        assert.equal(res.data, {
            id: 5,
            description: 'Test Password',
            type: 'password',
        }, 'Added password does not match.');

        //Update credential
        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                type: 'key',
                key: '-----BEGIN PUBLIC KEY-----\nMDwwDQYJKoZIhvcNAQEBBQADKwAwKAIhAMTyWha8C93l2NAPMkLPZ2WnbkqWXOnH\no3RenmVJHn1tAgMBAAE=\n-----END PUBLIC KEY-----'
            }
        });

        assert.equal(res.status, 204, 'Updating record should succeed.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Updated credential should be found.');
        assert.equal(res.data, {
            id: 4,
            description: 'Test Key',
            type: 'key',
            key: '-----BEGIN PUBLIC KEY-----\nMDwwDQYJKoZIhvcNAQEBBQADKwAwKAIhAMTyWha8C93l2NAPMkLPZ2WnbkqWXOnH\no3RenmVJHn1tAgMBAAE=\n-----END PUBLIC KEY-----'
        }, 'Updated key does not match.');

        // Change type to password
        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                description: 'Foo Bar',
                type: 'password',
                password: 'foo'
            }
        });

        assert.equal(res.status, 204, 'Updating record should succeed.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Updated credential should be found.');
        assert.equal(res.data, {
            id: 4,
            description: 'Foo Bar',
            type: 'password'
        }, 'Added key does not match.');

        //Test update fails
        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                type: 'foo'
            }
        });
        assert.equal(res.status, 400, 'Invalid type should trigger error.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                type: 'key',
                key: 'foo'
            }
        });
        assert.equal(res.status, 400, 'Invalid key should trigger error.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                type: 'key'
            }
        });
        assert.equal(res.status, 422, 'Missing key should trigger error.');

        var res = await req({
            url: '/records/1/credentials/4',
            method: 'put',
            data: {
                type: 'password'
            }
        });
        assert.equal(res.status, 422, 'Missing password should trigger error.');

        var res = await req({
            url: '/records/1/credentials/100',
            method: 'put',
            data: {
                description: 'foo'
            }
        });
        assert.equal(res.status, 404, 'Invalid credential should trigger error.');


        //Delete entry
        var res = await req({
            url: '/records/1/credentials/4',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of entry should succeed.');

        //Delete not existing entry
        var res = await req({
            url: '/records/1/credentials/100',
            method: 'delete'
        });

        assert.equal(res.status, 404, 'Deletion of not existing entry should fail.');

        //Delete entry via wrong record
        var res = await req({
            url: '/records/4/credentials/5',
            method: 'delete'
        });

        assert.equal(res.status, 404, 'Deletion of entry via wrong record should fail.');

    });

    await test('user', async function (assert, req) {
        //Add password with missing permissions
        var res = await req({
            url: '/records/4/credentials',
            method: 'post',
            data: {
                description: 'Test Password',
                type: 'password',
                password: 'foo'
            }
        });

        assert.equal(res.status, 403, 'Adding password should fail for missing permissions.');

        //Add password with missing permissions
        var res = await req({
            url: '/records/1/credentials',
            method: 'post',
            data: {
                description: 'Test Password',
                type: 'password',
                password: 'foo'
            }
        });

        assert.equal(res.status, 201, 'Adding password should succeed for user.');
        assert.equal(res.data, {
            id: 6,
            description: 'Test Password',
            type: 'password',
        }, 'Adding credential data fail.');

        //Delete entry
        var res = await req({
            url: '/records/1/credentials/6',
            method: 'delete'
        });

        assert.equal(res.status, 204, 'Deletion of entry should succeed for user.');

        //Delete entry without permission
        var res = await req({
            url: '/records/4/credentials/2',
            method: 'delete'
        });

        assert.equal(res.status, 403, 'Deletion of entry without permission should fail.');
    });
});