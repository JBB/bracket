#!/usr/local/bin/perl

use strict;

##see who has picked what team to go where
##see certain coaches
## see who has correctly picked the most upsets

read(STDIN, my $buffer, $ENV{'CONTENT_LENGTH'});

my ($sec,$min,$hour,$mday,$mon,$year,$wday,$yday,$isdst) = localtime(time);
my $theTeam="Stanford";
my $theOutFile = "out12";
my $thePicksFile = "picks12";
my $standingsURL = "standings.cgi";
my @pairs = split(/&/, $buffer);
foreach my $pair (@pairs)
{
  my ($name, $value) = split(/=/, $pair);
  $value =~ s/%([A-Za-z0-9]{2})/chr hex $1/ge;
  $value =~ s/\+/ /g;

  if ($name eq "team"){
     $theTeam = $value; 
  }elsif ($name eq "outFile"){
     $theOutFile = $value;
  }elsif ($name eq "picksFile"){
     $thePicksFile = $value;
  }elsif ($name eq "standingsURL"){
     $standingsURL = $value;
  }
}
$theTeam =~ s/\n//g;
my $stateTeam = $theTeam . " St.";
opendir DIR, "./$theOutFile";
my @files = map "$_", grep /\.out/, readdir DIR;
closedir DIR;

my %team;
my %numPicksPerTeam;
my @winsAndCoach;

my $file;
foreach $file (@files){
	my $i = 0;
	open(OUT,"./$theOutFile/$file");
	my $counter = 0;
	while(<OUT>){
		if ($counter > 0){
			my ($round,$team) = split(/\t/,$_);
			if (($team =~ /$theTeam/o) && !($team =~ /$stateTeam/)){
				$i++;
			}
		}
		$counter++;
	}
	$file =~ s/\.out/\.htm/;
	my $coach = $file;
	$coach =~ s/\.htm//;
	$team{$i} .= ", <a href=./$thePicksFile/$file>$coach</a>";
	$numPicksPerTeam{$i}++;
}

print "Content-type: text/html\n\n";
print "<HTML><HEAD><TITLE>Team Popularity</TITLE></HEAD>\n";
print "<body BGCOLOR=\"#ffa702\">\n";

my %rounds = (0,"Lose Opening Game",1,"Reach Round Of 32",2,"Reach Sweet Sixteen",3,"Reach Elite 8",4,"Reach Final Four",5,"Finalist",6,"Winner\,Winner\,Chicken Dinner!");
print "<center>\n";
print "<h2>Selections Chart for $theTeam</h2><br>\n";
print "<table border=1><tr><td align=center><b># Of Wins</b></td><td align=center><b>Round</b></td><td align=center><b># Of Picks For That Round</b></td><td align=center><b>Prognosticators</b></td></tr>\n";

foreach my $key (sort keys %team){
	$team{$key} =~ s/,//;
	print "<tr><td align=center>$key</td><td align=center>$rounds{$key}</td><td align=center>$numPicksPerTeam{$key}</td><td align=center>$team{$key}</td><tr>\n";
}
print "</table>\n";
print "<p><A href=$standingsURL>Back to Standings</a>\n";
print "</center>\n";
print "</body></HTML>";

