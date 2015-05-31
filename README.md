Dynamic DNS System Web End Point
================================

This project is a php based web endpoint for updating a small scale personal dynamic dns system. The system is based on `apache`, `php` and `bind9`.

### What is a dynamic DNS System? Why do I need one?

Most home users receive a temperory IP adderss on connecting to their ISP networks. This poses a problem for users to connect to their servers hosted at home from an external network. If the IP address changes, the user is stranded. Even without the IP address changing, it is hard to remember a sequence of numbers. This is the reason why the Domain Name System was created.

A dynamic DNS service lets you host and update your own name servers with a quick refresh time (seconds). The usual refresh times are in the order of several hours to a few days.

Examples of a few commercial services are - noip, dyndns, etc.

### What do I need to get one up and running?

To get one up and running, you need to install apache web server, bind9 dns server and php.

## Quick Setup
1. Install `apache`
2. Install `php`
3. install `bind9`
4. modify `/etc/bind/named.conf`. Add the following line - `include "/etc/bind/l2.example.com.zone";`
5. create a file - `/etc/bind/l2.example.com.zone`. add the following lines -
    ```
    zone "l2.example.com" IN {
      type master;
      file "l2.example.com.zoneRecords";
      allow-update { none; };
    };
    ```

6. create a file `/var/cache/bind/l2.example.com.zoneRecords`. Add a `Start of Authority` or `SOA` section followed by `A` records. An example is given below -

    ```
    $ORIGIN l2.example.com.
    $TTL 6s
    @       IN      SOA     ns.l2.example.com.        user.example.com.(
            1433051706
            30
            20
            3600
            6
    )
    @       IN NS ns

    host1 IN A 76.175.30.168
    host2 IN A 76.175.30.169
    ns IN A 50.10.110.120
    ```

7. Now, `apache` needs to get  the `bind9` service to reload the zone records. This needs the apache process to be able to execute `sudo service bind9 reload` without the password. Apache can be granted this permission by `sudoers` file.
  * Run `sudo visudo`

  * add `www-data ALL=(ALL) NOPASSWD: /usr/sbin/service bind9 reload`.

  * add `Defaults:www-data !requiretty` - this allows `sudo` to run without a `TTY`.

  * With changed permission, you need to stop and start tho apache web server - `sudo service apache2 stop` and `sudo service apache2 start`.

8. Modify the `keyFile.json`. This file has the shared keys to prevent unauthorized users from updating a record.

    ```
    {
            "host1":"alphaNumericSharedKey1",
            "host2":"alphaNumericSharedKey2",
            "host3":"alphaNumericSharedKey3"
    }
    ```

9. To update a DNS record, run `./dynUpdate` on various host. On a home network, it updates a name with the external IP address. Modify `dynUpdate` with the relevant configuration.

    ```
    #! /bin/bash
    HOST="<host1>"
    SHARED_KEY="<alphaNumericSharedKey1>"
    ENDPOINT="http://example.com/dynDnsEP/"
    TS=$(date +%s)
    VS=$HOST$TS$SHARED_KEY
    OP="$(echo -ne $VS| sha256sum | head -c64)";
    PS="HOST=$HOST&SIG=$OP&TS=$TS"
    curl --silent --data $PS $ENDPOINT
    echo -e ''
    ```
