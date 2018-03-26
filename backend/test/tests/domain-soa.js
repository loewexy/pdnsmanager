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

        //Set soa for zone without one
        var res = await req({
            url: '/domains/1/soa',
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

        assert.equal(res.status, 204, 'Updating SOA for Zone without one should succeed.');
    });
});