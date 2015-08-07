= BagIt

== Using Fetch files

Install a client certificate bundle somewhere.

=== Mac Ports

`sudo port install curl-ca-bundle`

Then, in the code:

    $fetch = new Fetch();
    $fetch->setCertPath('/opt/local/share/curl/curl-ca-bundle.crt');
