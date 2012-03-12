#!/usr/local/bin/perl

($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
my $stop = 0;

if (($mday == 17) && ($hour >= 17)) { ##GMT
	print "Location: http://www.empyre.com/ncaa/toolate.htm\n\n";
	$stop = 1;
}

if ($mday > 17) {
	print "Location: http://www.empyre.com/ncaa/toolate.htm\n\n";
	$stop = 1;
}
if ($mon != 2){
	print "Location: http://www.empyre.com/ncaa/toolate.htm\n\n";
	$stop = 1;
}

read(STDIN, $buffer, $ENV{'CONTENT_LENGTH'});

if ($stop == 0) { #begin if statement

@pairs = split(/&/, $buffer);
my @picks;
my $j = 0;
my $i = 0;
my $person;
my $password;
my $token; #could use this to validate human submitted form
my @arr; 
my %teams;

foreach $pair (@pairs)
{
  local($name, $value) = split(/=/, $pair);
  $value =~ s/%([A-Za-z0-9]{2})/chr hex $1/ge;
  $value =~ s/\+/ /g;
  $value =~ s/\'//g;
  $value =~ s/\&/and/g;

 if ($name eq "username")
  {
    $value =~ s/\s//g;
    if (($value eq "")||($value =~ /\@/)){
	print "Location: http://www.empyre.com/ncaa/noName.htm\n\n";
        exit 0;
    }
    $value =~ s/"//g;
    $value =~ s/'//g;
    $person = $teams{'person'} = $value;
  }

 if ($name eq "password")
  {
    $value =~ s/%([A-Za-z0-9]{2})/chr hex $1/ge;
    $value =~ s/\+/ /g;
    $password = $value; 
    #$value =~ s/\s//g;
    #$password = lc($value); 
    #if ($value ne "jay"){
    #  print "Location: http://www.empyre.com/ncaa/passWd.htm\n\n";
    #  exit 0;
    #}
  }

 if ($name eq "token")
  {
    #if token isn't what it should be, exit
    $value =~ s/\s//g;
    $value = lc($value); 
    if ($value ne "jay"){
      print "Location: http://www.empyre.com/ncaa/passWd.htm\n\n";
      exit 0;
    }
  }

  if ($name eq "picks")
  {
    #@arr = ($value =~ m/\w+/g);
    $value =~ s/%([A-Za-z0-9]{2})/chr hex $1/ge;
    $value =~ s/\+/ /g;
    $value =~ s/\'//g;
    @arr = split(/\,/, $value);

    for($j=0;$j<=$#arr;$j++){
      #chomp($arr[$i+1]);
      $picks[$j] = "$arr[$i]\t$arr[$i+1]";
      #$arr[$i+1] =~ s/%([A-Za-z0-9]{2})/chr hex $1/ge;
      #$arr[$i+1] =~ s/\+/ /g;
      $teams{$arr[$i]} = $arr[$i+1];
      $j++;
      $i = $i + 2;
    }
    $picks[$j] = "username\t$person";
    $j++;
    $picks[$j] = "password\t$password";
    $j++;
  }

}
#check to see if we have an entry by that name and if edit is not authorized
my $filename = "./out12/$person.out";
my $filename2 = "./out12/$person.tmp";
if ((-e $filename) && (! -e $filename2)) {
  print "Location: http://www.empyre.com/ncaa/entryByThatNameExists.htm\n\n";
  exit 0;
}
`rm $filename2`;
#end check

open(OUT, ">./out12/$person.out");
for($j=0;$j<=$#picks;$j++){
  print OUT "$picks[$j]\n";
}
close OUT;

##Do some logging##
open(LOG, ">>./out12/ncaa.log");
print LOG "$teams{person} from $ENV{'REMOTE_ADDR'} at $mon/$mday$, $hour:$min:$sec GMT\n";
close LOG;
##End of logging##
 		 
open(PICKS, ">./picks12/$teams{person}.htm");
print PICKS "<HTML><HEAD><TITLE>INDIVIDUAL SELECTIONS</TITLE></HEAD>\n";
print PICKS "<body BGCOLOR=\"#ffa702\">\n";
print PICKS "$teams{person}\n";
print PICKS "'s picks...<br>\n";

#JBB - Edit header row to reflect appropriate names for the quarters
print PICKS "<p><table border=1 cellspacing=2><tr bgcolor=\"#3b4676\"><td colspan=4 align=center><b>Round of 32</b></td></tr>\n";
print PICKS "<tr><td align=center>West</td><td align=center>East</td><td align=center>Midwest</td><td align=center>South</td></tr>\n";
#JBB Begin 2nd round
print PICKS "<tr><td align=center>";
print PICKS "$teams{q1r2g1t1}<br>$teams{q1r2g1t2}<br>$teams{q1r2g2t1}<br>$teams{q1r2g2t2}<br>$teams{q1r2g3t1}<br>$teams{q1r2g3t2}<br>$teams{q1r2g4t1}<br>$teams{q1r2g3t2}<br>";
print PICKS "<br></td><td align=center>";
print PICKS "$teams{q2r2g1t1}<br>$teams{q2r2g1t2}<br>$teams{q2r2g2t1}<br>$teams{q2r2g2t2}<br>$teams{q2r2g3t1}<br>$teams{q2r2g3t2}<br>$teams{q2r2g4t1}<br>$teams{q2r2g3t2}<br>";
print PICKS "<br></td><td align=center>";
print PICKS "$teams{q3r2g1t1}<br>$teams{q3r2g1t2}<br>$teams{q3r2g2t1}<br>$teams{q3r2g2t2}<br>$teams{q3r2g3t1}<br>$teams{q3r2g3t2}<br>$teams{q3r2g4t1}<br>$teams{q3r2g3t2}<br>";
print PICKS "<br></td><td align=center>";
print PICKS "$teams{q4r2g1t1}<br>$teams{q4r2g1t2}<br>$teams{q4r2g2t1}<br>$teams{q4r2g2t2}<br>$teams{q4r2g3t1}<br>$teams{q4r2g3t2}<br>$teams{q4r2g4t1}<br>$teams{q4r2g3t2}<br>";
print PICKS "</td></tr>\n";
#JBB End 2nd round
#JBB Begin Rd of 16
print PICKS "<tr bgcolor=\"#3b4676\"><td colspan=4 align=center><b>Sweet 16</b></td></tr>\n";
#JBB - Edit header row to reflect appropriate names for the quarters
print PICKS "<tr><td align=center>West</td><td align=center>East</td><td align=center>Midwest</td><td align=center>South</td></tr>\n";
print PICKS "<tr><td align=center>";
print PICKS "$teams{q1r3g1t1}<br>$teams{q1r3g1t2}<br>$teams{q1r3g2t1}<br>$teams{q1r3g2t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q2r3g1t1}<br>$teams{q2r3g1t2}<br>$teams{q2r3g2t1}<br>$teams{q2r3g2t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q3r3g1t1}<br>$teams{q3r3g1t2}<br>$teams{q3r3g2t1}<br>$teams{q3r3g2t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q4r3g1t1}<br>$teams{q4r3g1t2}<br>$teams{q4r3g2t1}<br>$teams{q4r3g2t2}<br>";
print PICKS "</td></tr>\n";
#JBB End of Rd of 16
#JBB Begin Great 8
print PICKS "<tr bgcolor=\"#3b4676\"><td colspan=4 align=center><b>Great 8</b></td></tr>\n";
#JBB - Edit header row to reflect appropriate names for the quarters
print PICKS "<tr><td align=center>West</td><td align=center>East</td><td align=center>Midwest</td><td align=center>South</td></tr>\n";
print PICKS "<tr><td align=center>";
print PICKS "$teams{q1r4g1t1}<br>$teams{q1r4g1t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q2r4g1t1}<br>$teams{q2r4g1t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q3r4g1t1}<br>$teams{q3r4g1t2}<br>";
print PICKS "</td><td align=center>";
print PICKS "$teams{q4r4g1t1}<br>$teams{q4r4g1t2}<br>";
print PICKS "</td></tr>\n";
#JBB End Great 8
#Begin Final 4
print PICKS "<tr bgcolor=\"#3b4676\"><td colspan=4 align=center><b>Final Four</b></td></tr>\n";
#JBB - Edit header row to reflect appropriate names for the quarters
print PICKS "<tr><td align=center>West</td><td align=center>East</td><td align=center>Midwest</td><td align=center>South</td></tr>\n";
print PICKS "<tr><td align=center>$teams{ff1r1g1t1}</td><td align=center>$teams{ff1r1g1t2}</td><td align=center>$teams{ff1r1g2t1}</td><td align=center>$teams{ff1r1g2t2}</td></tr>\n";
print PICKS "<tr bgcolor=\"#3b4676\"><td colspan=2 align=center><b>Champion</b></td><td colspan=2 align=center><b>Runner Up</b></tr>\n";
print PICKS "<tr><td colspan=2 align=center>$teams{ff1r2g1t1}</td><td colspan=2 align=center>$teams{ff1r2g1t2}</td></tr>\n";
print PICKS "</table><p>\n";
		 		 
print PICKS "<p><A href=\"../standings.cgi\">Back to Standings</a>\n";
print PICKS "</HTML>\n";
close PICKS;


open(ROSTER, ">>./out12/roster.dat");
print ROSTER "$teams{person}\n";
close ROSTER;

print "Content-type: text/html\n\n";
print "<HTML><HEAD>\n";
#print "<meta HTTP_EQUIV=\"Refresh\" Content=\"5\" URL=\"standings.html\">\n";
print "<Title>Picks Submitted!</Title></HEAD>\n";
print "<body BGCOLOR=\"#ffa702\"><center>\n";
print "<h2>Thanks and welcome to the pool!</h2><br>\n";
print "Bookmark the <a href=\"standings.cgi\"> standings </a> page to see how your picks measure up against others and to contribute to the chat room as the tournament progresses.\n";
print "<p> See you in there!\n";
print "<p></center>\n";
print "</center>\n";
print "</body></html>";

} #end if statement

