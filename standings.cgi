#!/usr/local/bin/perl


use Fcntl ':flock';


my $now = localtime;
($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
my $editOK = 1;

if (($mday == 17) && ($hour >= 17)) { ##GMT
	$editOK= 0;
}

if ($mday > 17) {
	$editOK = 0;
}
if ($mon != 2){
	$editOK = 0;
}

print "Content-type: text/html\n\n";
print "<HTML><HEAD>\n";
print "<Title>NCAA Tournament 2011 Standings as of $now GMT </Title>\n";

print "<link rel=\"stylesheet\" href=\"css/style.css\">";
print "</HEAD>\n";
print "<BODY BGCOLOR=\"#FFA702\" text=\"000000\" link=\"880000\" vlink=\"880000\" alink=\"880000\">\n";
print "<center>\n";

print "<script LANGUAGE=\"JavaScript\">\n";
print "function openit() {\n";
print "newWindow = window.open('./talk.cgi?$coach=1','Hoops_Chat_Room','scrollbars,resizable,width=600,height=450')\;\n";
print "}\n";
print "</script>\n";

print "<a href=\"javascript:openit()\;\"><font size=+2>***** Chat Room *****</font></a><br>\n";
print "<a href=\"standings98.html\">1998 Results</a><br>";
print "<a href=\"standings99.html\">1999 Results</a><br>";
print "<a href=\"standings2000.htm\">2000 Results</a><br>";
print "<a href=\"standings2001.html\">2001 Results</a><br>";
print "<a href=\"standings2002.html\">2002 Results</a><br>";
print "<a href=\"standings2003.html\">2003 Results</a><br>";
print "<a href=\"standings2004.html\">2004 Results</a><br>";
print "<a href=\"standings2005.html\">2005 Results</a><br>";
print "<a href=\"standings2006.html\">2006 Results</a><br>";
print "<a href=\"standings2007.html\">2007 Results</a><br>";
print "<a href=\"standings2008.html\">2008 Results</a><br>";
print "<a href=\"standings2009.html\">2009 Results</a><br>";
print "<a href=\"standings2010.html\">2010 Results</a><br>";
print "<a href=\"standings2011.html\">2011 Results</a><br>";
print "<a href=\"bobby.wav\">Bobby Knight Tirade (rated R)</a><br><br>";

print "<form action=\"stats12.cgi\" method=Post>\n";
print "<SELECT name=\"team\" onChange=\"submit();\">\n";
print "<OPTION SELECTED VALUE=\"\">Choose a team to see selection spread\n"; 
open(TEAMS, "./out12/teams.dat");
while(<TEAMS>){
        ($tm,$extra) = split(/\n/,$_);
	print "<OPTION VALUE=\"$tm\">$tm</option>\n";
}
close TEAMS;
print "</SELECT>\n";
print "<input type=hidden name=outFile value=out12>\n";
print "<input type=hidden name=picksFile value=picks12>\n";
print "<input type=hidden name=standingsURL value=standings.cgi>\n";
print "</form>\n";

open LOG, ">>./out12/ncaa.log";
flock LOG, LOCK_EX | LOCK_NB or print STDERR "Be patient, your request is in queue...";
flock LOG, LOCK_EX;

my $person;
my ($code,$team);
my %rank;
my %picks;
my $total;
open(OUT, "./out12/roster.dat");
while (<OUT>){
	$person = $_;
	$person =~ s/\s//g;
	$total = 0;

	open(OUTB, "./out12/$person.out");
	while (<OUTB>){	
		($code,$team) = split(/\t/, $_);
		$picks{$code} = $team;
	}
	close OUTB;

	if ($picks{q1r2g1t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g1t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g2t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g2t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g3t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g3t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g4t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q1r2g4t2} =~/xxx/){$total = $total + 1;}

	if ($picks{q2r2g1t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g1t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g2t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g2t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g3t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g3t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g4t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q2r2g4t2} =~/xxx/){$total = $total + 1;}

	if ($picks{q3r2g1t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g1t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g2t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g2t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g3t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g3t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g4t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q3r2g4t2} =~/xxx/){$total = $total + 1;}

	if ($picks{q4r2g1t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g1t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g2t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g2t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g3t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g3t2} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g4t1} =~/xxx/){$total = $total + 1;}
	if ($picks{q4r2g4t2} =~/xxx/){$total = $total + 1;}


	if ($picks{q1r3g1t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q1r3g1t2} =~/xxx/){$total = $total + 2;}
	if ($picks{q1r3g2t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q1r3g2t2} =~/xxx/){$total = $total + 2;}

	if ($picks{q2r3g1t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q2r3g1t2} =~/xxx/){$total = $total + 2;}
	if ($picks{q2r3g2t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q2r3g2t2} =~/xxx/){$total = $total + 2;}

	if ($picks{q3r3g1t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q3r3g1t2} =~/xxx/){$total = $total + 2;}
	if ($picks{q3r3g2t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q3r3g2t2} =~/xxx/){$total = $total + 2;}

	if ($picks{q4r3g1t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q4r3g1t2} =~/xxx/){$total = $total + 2;}
	if ($picks{q4r3g2t1} =~/xxx/){$total = $total + 2;}
	if ($picks{q4r3g2t2} =~/xxx/){$total = $total + 2;}

	if ($picks{q1r4g1t1} =~/xxx/){$total = $total + 3;}
	if ($picks{q1r4g1t2} =~/xxx/){$total = $total + 3;}
	if ($picks{q2r4g2t1} =~/xxx/){$total = $total + 3;}
	if ($picks{q2r4g2t2} =~/xxx/){$total = $total + 3;}
	if ($picks{q3r4g1t1} =~/xxx/){$total = $total + 3;}
	if ($picks{q3r4g1t2} =~/xxx/){$total = $total + 3;}
	if ($picks{q4r4g2t1} =~/xxx/){$total = $total + 3;}
	if ($picks{q4r4g2t2} =~/xxx/){$total = $total + 3;}


	if ($picks{ff1r1g1t1} =~/xxx/){$total = $total + 4;}
	if ($picks{ff1r1g1t2} =~/xxx/){$total = $total + 4;}
	if ($picks{ff1r1g2t1} =~/xxx/){$total = $total + 4;}
	if ($picks{ff1r1g2t2} =~/xxx/){$total = $total + 4;}

	if ($picks{ff1r2g1t2} =~/xxx/){$total = $total + 5;}
	if ($picks{ff1r2g1t1} =~/xxx/){$total = $total + 6;}

	$rank{$person} = $total;
}
close OUT;
my $count =0;
my $margin =0;
my $marginCheck =0;

print "<table width=100% border=0 cellspacing=6>\n";
print "<td align=left width=15%></td><td align=center width=70%>\n";


my @standings = sort {$b->[1] <=> $a->[1]} map { [ $_,$rank{$_} ] } keys %rank;
print "<br><TABLE border=1 cellspacing=1>\n";
print "<tr><td align=center width=10%><b>Rank</b></td><td align=center width=50%><b>Coach</b></td><td align=center width=15%><b>Wins</b></td><td align=center width=25%><b>View Picks</b></td></tr>\n";

#foreach $key (sort keys %rank){
while ($count <= $#standings){

	if ($standings[$count][1] == $marginCheck){
		$margin++;
	}else{
		$margin = 0;
	}
	my $place = $count + 1 - $margin;
	print "<tr><td align=center width=10%>$place</td><td align=center width=50%>$standings[$count][0]</td><td align=center width=15%>$standings[$count][1]</td><td align=center width=25%><a href=\"./picks12/$standings[$count][0].htm\">View Picks</a>";
        if ($editOK == 1) {
          print " | <a href=\"#\" onclick=\"document.getElementById('$standings[$count][0]').style.display='';\">Edit Picks</a>\n";
          print "<div id=\"$standings[$count][0]\" style=\"display: none;\">\n";
          print "<form action=\"edit_picks.php\" method=\"POST\">\n";
          print "<input type=hidden name=\"username\" value=\"$standings[$count][0]\">\n";
          print "<input type=hidden name=\"token\" value=\"jay\">\n";
          print "<input type=password name=\"password\" placeholder=\"password\">\n";
          print "<input type=submit value=\"Edit Picks\">\n";
          print "</form>\n";
          print "</div>\n";
        } 
        print "</td></tr>\n";
	
        $marginCheck = $standings[$count][1];
	$count++;
	
}
print "</table>\n<br>\n";
print "</td><td align=right width=15%></td></tr></table>\n";


print "</center>\n";

close LOG;

print "<script>\n";
print "function showForm() {\n";
print "document.getElementById('editPicks').style.display=\"show\";\n";
print "}\n";
print "</script>\n";

print "</body></html>\n";

