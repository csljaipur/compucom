
use IO::Socket::INET;
use DBI;
# flush after every write
$| = 1;

my ($socket,$client_socket);
my ($peeraddress,$peerport);

my $host = "127.0.0.1";
my $database = "csl_db";
my $tablename = "pt_position";
my $user = "root";
my $pw = "";
my $dbh = DBI->connect('DBI:mysql:csl_db', $user, $pw
	           ) || die "Could not connect to database: $DBI::errstr";
			   
			   
# creating object interface of IO::Socket::INET modules which internally does
# socket creation, binding and listening at the specified port address.
$socket = new IO::Socket::INET (
LocalHost =>  '127.0.0.1',
LocalPort =>  '7070',
Proto =>  'tcp',
Listen =>  5,
Reuse =>  1
) ;

print "SERVER Waiting  port 7070";
$client_socket = $socket->accept();
print "Connected on  port 7070";
while(1)
{

$client_socket->recv($line,100);

print "$line\n";
my @data=split(/,/,$line);
my $flag = index($data[14],"A#") ;
if ($flag >= 0)
{
my $unit_id = $data[1];
my $date = $data[4]+2000 . "-" . $data[3] . "-" . $data[2] . " " . $data[5] . ":" . $data[6] . ":" . $data[7];


	my @lat = split(/[NS]/,$data[8]);
	my $latmin = $lat[0] % 1000000 ;
	my $latdeg =  ( $lat[0] - $latmin ) / 1000000;
	$latmin =  ( $latmin /  60 ) * 100000000000 ;
	my $latstr = sprintf("%.0f", $latmin);
	my @latmin_a = split(/"."/,$latstr);
	my $lattitude = $latdeg . "." .  $latmin_a[0] ;
	my $ret = index($data[8],"S") ;
	if ( $ret >= 0 ) {
		$lattitude = -$lattitude ;
	}



my @lon = split(/[EW]/,$data[9]);
	my $lonmin = $lon[0] % 1000000 ;
	my $londeg =  ( $lon[0] - $lonmin ) / 1000000;
	$lonmin =  ( $lonmin /  60 ) * 100000000000 ;
	my $lonstr = sprintf("%.0f", $lonmin);
	my @lonmin_a = split(/"."/,$lonstr);
	my $longitude = $londeg . "." .  $lonmin_a[0] ;
	my $ret = index($data[9],"W") ;
	if ( $ret >= 0 ) {
		$longitude = -$longitude ;
	}

##########################################


my $speed_km = $data[10]; 
my $speed_kn = $data[10] / 1.852;
my $deg = $data[11];
my $fixtype = 3;

print $unit_id; print "\n";
print $date; print "\n";
print $lattitude; print "\n";
print $longitude; print "\n";
print $deg; print "\n";
print $speed_km; print "\n";
print $speed_kn; print "\n";



  my $sth = $dbh->prepare("INSERT INTO $tablename(`id`, `unit_id`, `datetime`, `datetime_received`, `lat`, `lon`, `alt`, `deg`, `speed_km`, `speed_kn`, `sattotal`, `fixtype`, `raw_input`, `hash`) VALUES (DEFAULT, ?, ?, ?, ?, ?, 62, ?, ?, ?, 1, ?, ?, '')");
	
	$sth->execute($unit_id, $date, $date,$lattitude,$longitude, $deg, $speed_km, $speed_kn, $fixtype, $line);
 } 	

}
$dbh->disconnect();
$socket->close();
