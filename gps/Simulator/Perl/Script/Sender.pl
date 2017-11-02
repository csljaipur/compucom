use IO::Socket;
my $sock = new IO::Socket::INET (
                                PeerAddr => '127.0.0.1',
                               PeerPort => '7070',
                               Proto => 'tcp',
                             );
die "Could not create socket: $!\n" unless $sock;
 
 
$data_file = "data.txt";

if (open(FH, "$data_file")) {

while ($line = <FH>) {

print $line;
print $sock $line; 
sleep 10;
}

}
else {
print "Unable to open the file";
}
close (FH);
close($sock);

