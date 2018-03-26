const test = require('../testlib');

test.run(async function () {
    await test('admin', async function (assert, req) {
        //Try to set soa for non exitent domain
        var res = await req({
            url: '/domains/100/soa',
            method: 'put',
            data: {
                primary: 'ns1.example.com',
                email: 'hostmaster@example.com',
                refresh: 3600,
                retry: 900,
                expire: 604800,
                ttl: 86400
            }
        });

        assert.equal(res.status, 404, 'Updating SOA for not existing domain should fail');

        //Try to set soa for slave domain
        var res = await req({
            url: '/domains/2/soa',
            method: 'put',
            data: {
                primary: 'ns1.example.com',
                email: 'hostmaster@example.com',
                refresh: 3600,
                retry: 900,
                expire: 604800,
                ttl: 86400
            }
        });

        assert.equal(res.status, 405, 'Updating SOA for slave domain should fail');

        //Try to set soa with missing fields
        var res = await req({
            url: '/domains/2/soa',
            method: 'put',
            data: {
                primary: 'ns1.example.com',
                retry: 900,
                expire: 604800,
                ttl: 86400
            }
        });

        assert.equal(res.status, 422, 'Updating SOA with missing fields should fail.');

        //Getting soa data from master zone without soa should fail
        var res = await req({
            url: '/domains/1/soa',
            method: 'get'
        });

        assert.equal(res.status, 404, 'Not existing soa should trigger error');

        //Getting soa data from slave zone should fail
        var res = await req({
            url: '/domains/1/soa',
            method: 'get'
        });

        assert.equal(res.status, 404, 'Geting soa from slave should trigger error');

        //Soa data for test
        var soaData = {
            primary: 'ns1.example.com',
            email: 'hostmaster@example.com',
            refresh: 3600,
            retry: 900,
            expire: 604800,
            ttl: 86400
        };

        //Set soa for zone without one
        var res = await req({
            url: '/domains/1/soa',
            method: 'put',
            data: soaData
        });

        assert.equal(res.status, 204, 'Updating SOA for Zone without one should succeed.');

        //Get the new soa
        var res = await req({
            url: '/domains/1/soa',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Getting soa should succeed.');
        const firstSerial = res.data.serial;
        delete res.data['serial'];
        assert.equal(res.data, soaData, 'The set and get data should be equal');

        //Soa data for update test
        soaData = {
            primary: 'ns2.example.com',
            email: 'hostmasterFoo@example.com',
            refresh: 3601,
            retry: 901,
            expire: 604801,
            ttl: 86401
        };

        //Update soa with new values
        var res = await req({
            url: '/domains/1/soa',
            method: 'put',
            data: soaData
        });

        assert.equal(res.status, 204, 'Updating SOA for Zone should succeed.');

        //Check if update suceeded
        var res = await req({
            url: '/domains/1/soa',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Getting updated soa should succeed.');
        assert.true(firstSerial < res.data.serial, 'Serial value should increase with update');
        delete res.data['serial'];
        assert.equal(res.data, soaData, 'The set and get data should be equal after update');
    });

    await test('user', async function (assert, req) {
        //Soa data for test
        var soaData = {
            primary: 'ns1.example.com',
            email: 'hostmaster@example.com',
            refresh: 3600,
            retry: 900,
            expire: 604800,
            ttl: 86400
        };

        //Updating soa for domain with permissions should work
        var res = await req({
            url: '/domains/1/soa',
            method: 'put',
            data: soaData
        });

        assert.equal(res.status, 204, 'Updating SOA for Zone should succeed for user.');

        //Get the updated soa
        var res = await req({
            url: '/domains/1/soa',
            method: 'get'
        });

        assert.equal(res.status, 200, 'Getting soa should succeed for user.');
        delete res.data['serial'];
        assert.equal(res.data, soaData, 'The set and get data should be equal');

        //Updating soa for domain with permissions should work
        var res = await req({
            url: '/domains/4/soa',
            method: 'put',
            data: soaData
        });

        assert.equal(res.status, 403, 'Updating SOA for Zone without permissions should fail.');
    });
});