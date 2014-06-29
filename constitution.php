<?php
  require_once 'util/sessions.php';

  $redirectUrl = "constitution.php";
  SessionUtil::logoutUserIfNotLoggedIn($redirectUrl);
?>

<!DOCTYPE html>
<html>

<?php
  require_once 'util/layout.php';
  LayoutUtil::displayHeadTag("Constitution", true);
?>
<body>

<?php
  // Nav bar
  LayoutUtil::displayNavBar(true, LayoutUtil::CONSTITUTION_BUTTON);
?>

<div class='row-fluid'>
  <div class='span12 center'>
    <h3>League Constitution</h3>
  </div>
</div>
<div class='row-fluid'>
  <div class='span12'>
    <br/>
    <div class='row-fluid'>
      <div class='span6'>
	    <h4>Table of Contents</h4>
	    <ul>
	      <li><a href='#a1'>Article One: The Draft</a></li>
	      <li><a href='#a2'>Article Two: Schedule & Playoffs</a></li>
	      <li><a href='#a3'>Article Three: Trade Deadline</a></li>
	      <li><a href='#a4'>Article Four: Fees and Payouts</a></li>
	      <li><a href='#a5'>Article Five: The Committee</a></li>
	      <li><a href='#a6'>Article Six: Keeper System</a></li>
	      <li><a href='#a7'>Article Seven: Auction</a></li>
	      <li><a href='#a8'>Article Eight: The Winter Meetings</a></li>
	      <li><a href='#a9'>Article Nine: Rosters</a></li>
	      <li><a href='#a10'>Article Ten: The Offseason and Misc items</a></li>
	    </ul>
      </div>
    </div>
    <p>
      <strong>We the people of St. Petersburg Keepatorium, in order to form a more perfect fantasy league, do ordain and establish this constitution.</strong>

<a id='a1'></a><h4>Article One: The Draft</h4>
<p>
Players shall be distributed through the dynamic market mechanism of a league draft. All free agents are fit to be selected, and will be so allocated one at a time, at a predetermined time and place in a predetermined order.  At every moment of the draft, there will be one and only team granted the privilege of the podium.  When a selection is made, the privilege of the podium will be handed to the next in line.  Foodstuffs and spirits should be bountiful, and consumed with great vigor and merriment.
<h5>Section 1:</h5> The draft will consist of a maximum of 23 rounds, each round will contain 16 distinct selections corresponding to the 16 distinct teams. Picks are to be made in a timely manner, failure to do so will result in torment and ostracism.

<h5>Section 2:</h5> Each year's draft order from now until eternity will be established starting at round 5 by a random number generator or similar device.  A fresh random order will be made for each odd numbered round.  Each even numbered round will be an inverse order of the round preceding it.  For instance, the team that selects first in the 5th round, will select last in the 6th. Rounds 3 and 4 will be determined at Winter Meetings in a manner to be decided there. Rounds 1 and 2 will be decided by the "run for the roses," the Kentucky Derby, with each team assigned a horse from the field. 

<h5>Section 3:</h5> A supplemental "ping pong ball" round will be appended to the start of the draft.  Ping pong ball picks are auctioned off to the highest bidder, with a minimum bid of $125 from the team's yearly budget.  Any bid of at least that amount will grant a team a ping pong ball, with the highest bid earning the right to pick first, and so on.  In the event of two or more identical bids, the team with the worst regular season record from the previous year will select first.  Once all the ping pong ball picks are made, the first round will commence.  

<h5>Section 4:</h5> An owner may have a maximum of 3 extra draft picks through the first 3 rounds.  This includes picks acquired through trade, purchased (including ping pong balls) or otherwise obtained in any way.     

<h5>Section 5:</h5> Until decided otherwise the draft will be held at Matt's house and he will make ribs.

<a id='a2'></a><h4>Article Two: Schedule and Playoffs</h4>

In every year the league remains active and vital, a champion will be crowned.  The title of St. Petersburg champion is eternal and represents the ultimate achievement of our collective enterprise.

<h5>Section 1:</h5> The regular season will consist of 20 weeks and a total of 60 games.  Scoring periods begin on Monday and conclude on Sunday. 

<h5>Section 2:</h5>  Each week, groups of 4 teams will play each other in round robin play.  A team's results for the week are determined by its score relative to the other 3 teams in the group.  Thus, each week every team will record three results.  

<h5>Section 3:</h5> Each team plays every other team in their division nine times, each of the teams in the other intraleague division six times, and each team from one division in the other league 3 times.  Each year, the interleague divisional matchups will alternate.  Divisions were decided in 2007 and remained unaltered until the great upheaval at the winter meetings of 2010 were it was decided that in 2011, and remaining until revisited, divisions would change from the existing "natural rivalry" grouping to "random cause we like random".

<h5>Section 4:</h5> Six of the sixteen teams will earn a spot in the postseason tournament.  All four divisional winners make the playoffs, and an additional "wildcard" team from each league (determined by best regular season record) will advance.  The team with the best regular season record in each league will earn a bye in the first round of the playoffs, the remaining division winner will play the wildcard team in a one-game elimination game.  The bye team will then play the winner of that game in the League Championship Game for the right to play in the World Series.  The World Series will consist of a 2 out-of 3 head-to-head matchup of the two league champions.  

<h5>Section 5:</h5> Ties in regular season and playoff games are resolved in the following order: Middle 7 player scores, middle 9, 11, 13, and finally total runs scored.

<h5>Section 6:</h5> Ties for playoff berth or seeding are to be resolved in the following order: Head-to-head record, total points scored for the year, total breakdown.


<a id='a3'></a><h4>Article Three: Trade Deadline</h4>

<h5>Section 1:</h5>  The trade deadline will be permanently set as the first lineup lock after the All-Star game superweek.


<a id='a4'></a><h4>Article Four: Fees and Payouts</h4>

Though we would play for nothing, we proudly lunge at each other's pockets.  

<h5>Section 1:</h5> The annual fee for the league is 100 dollars.  With 16 teams, the total pot is 1600 dollars.  The CBS website which administers our league costs 150 dollars.  Thus, our yearly prize pool is 1450 dollars.  The annual fee must be paid for each team by the draft or a $1,000,000 penalty will be enforced.

<h5>Section 2:</h5> Each division winner receives $100, and an additional $50 for  the bye.  The World Series loser gets a $200 consolation prize, and the winner of the league will win $625 in addition to any other prize money.  Highest point total for the regular season gets $50.  

<h5>Section 3:</h5>  Each week, the team with the lowest score will pay $10 to the team with the highest score.  This is a self-sustaining system, and does not affect any of the above fees or payouts.


<a id='a5'></a><h4>Article Five: The Committee</h4>

<h5>2007:</h5> In ancient Athens, every citizen was considered to be a "zoon politikon," roughly translated as "city beast" or political animal.  The same is true of our sacred confederacy, St. Petersburg Keepatorium (licensed trademark of St. Petersburg LLC. and the St. Petersburg Syndicate of Fantasy Leagues).  It is every owner's right and responsibility to have his voice heard and counted in the democratic forums which govern our league.  But alas, with time and prolonged success comes complacency.  Absenteeism and indifference has plagued our democracy in recent years, and it is with this in mind that we form The Committee.

<h5>2011:</h5> Committee abolished. Also polls will no longer be used for league business, we will have an online chat room that anyone who is interested in the issue being decided can have a say in its outcome.

<a id='a6'></a><h4>Article Six: Keeper System</h4>

Many years ago, in an oddly shaped hotel room in Montreal's Chinatown, a fantasy league was born.  With only cheap Canadian marijuana to spark their creativity and a pay-by-minute computer in the hotel lobby to measure Andy Pettitte's 2001 statistics against the unorthodox pitching formula they had crafted, the brave founders of this league had created something unlike anything that had come before it.  Word of the league quickly spread, and before long the league grew to sixteen teams.  But they did not stop there.  Just two years later, a unique and unprecedented ranking and keeping system was introduced.  And again, the league flourished.  Now the time has come to once again raise the bar.  I give to you, the new St. Petersburg Keeper Plan.  

<h5>Section 1:</h5> Teams are given a keeper budget of $450.  Teams may not spend funds from their budget until after the current year's rankings are released. Similarly teams may not trade money, heretofore referred to as Brognas ($), unless it is banked from a previous year until their bank refreshes once rankings are released. 

<h5>Section 2:</h5> After the season ends, all players will be ranked on a scale of 1-10.  During the ranking period, all rosters are frozen.  Owners will give who they believe to be the 15 best players a score of 10, the next 15 a score of 9, and so on for a total of 150 players.  Owners will not rank players on their own team.  A player's salary is determined by their total ranking assigned to them by the other 15 teams in the league.  For instance, if a player receives the following rankings: 6, 7, 5, 6, 2, 3, 8, 4, 6, 7, 7, 9, 3, 4, 5 then his salary for the coming year will be $83.  The maximum salary is $150, minimum salary is $30.  Placeholders will be assigned to players in the middle of multi-year deals to ensure the rankings are done in an upfront way.

<h5>Section 3:</h5> Ranking of players will begin upon the completion of the MLB World Series, and will be revealed Jan 1st of the new year.

<h5>Section 4:</h5> Once the rankings are revealed, owners may choose to keep any number of players on their team they wish, as long as they are within the $450 budget.  Remaining Brognas may be banked for next year (max of $150) or spent on a ping pong ball (see Article One, Sec 3).  Banked funds may be spent during the season to buy draft picks, sweeten trades, etc...  However, as stated above, teams may not dip into the $450 for next year.  

<h5>Section 5:</h5> Only keepers receive a salary.  Any drafted or picked up player (not already under contract) is free for that year and is owed no salary.  

<h5>Section 6:</h5> Owners may choose to sign keepers to either one or two-year contracts.  Therefore, including the draft year, players can stay on one team for up to 3 full years without returning to the draft pool.  

<h5>Section 7:</h5> In the offseason after a player's contract expires, he will be eligible for auction.

<h5>Section 8:</h5> If a player under contract for next year is traded or dropped and picked up, the new team is responsible for the remainder of the player's contract.  Contracts follow players, and are not erased when they switch teams.  

<h5>Section 9:</h5> All multiyear contracts are locked in at the original amount and do not change value regardless of injury or performance. This is the risk an owner takes when deciding to sign a player long term.

<h5>Section 10:</h5> If, for any reason, an owner does not wish to keep a player who is under contract, the player may be bought out for 50% of his remaining salary. For players being bought out with multiple years left, 50% must be paid for each remaining year.  Ex. Scott Kazmir signed for 2 years at $33 can only be bought out for $16.50 + $16.50 = $33. An owner can, however, drop them and hope that he is picked up which transfers the responsibility of the contract to the new owner.  

<h5>Section 11:</h5> As a parameter of the team budget, there will be a spending floor and spending ceiling.  With the addition or subtraction of the $450 budget via trading, by keeper night a teams total spent Brognas (player contracts, pp balls, bank) for the upcoming season must be no lower than $400, and no higher than $500.  If you acquire more than a total of $500 during the season, you must get rid of excess Brognas via trade by keeper night, or risk losing anything above the $500.  If you have $505, your total combined player contacts, pp ball bids and bank can not exceed $500, and therefore the extra $5 will disappear into the nether realms.
 
<h5>Section 12:</h5> The Seltzer rule simply stated is this, any player drafted after a predetermined slot, (currently the 201st player, when considering all players kept and drafted) can be kept immediately or at any point in the season on a sliding scale. Any player eligible to be Seltzered can be regardless of service time. Ex. a player can be Seltzered at the draft up until the end of week 1 for $33, by the end of week 2 for $36 etc.

<h5>Section 13:</h5> A minor league system was implemented at the urging of Brad based on West Coast principals.  Each teams roster can carry up to 3 minor leaguers. That is defined as someone who has never played in the majors or who is below the AB / IP limit. These players are eligible to be kept for free as long as they have not reached the AB / IP limit and have never been placed on a teams roster active or bench. 

<h5>Section 14:</h5> Minor league players may be Seltzer Juniored. The Seltzer Jr clock is for every 25 AB or 10 IP the value increases by 2 Brogna's. ex. Bryce Harper is called up and JR'ed before playing a game for $20. If the owner waited until he had 30 AB the price would be $22 etc.  

<h5>Section 15:</h5> At any point in time before a minor leaguer has been placed on a teams roster active or bench, he may be Seltzer Juniored for $15.  Once he is called up by the club and is placed on the MLB active roster or bench, the Seltzer Jr price goes to $20, and then the Seltzer Jr clock begins (see section 14) 


<a id='a7'></a><h4>Article Seven: Auction</h4>

<h5>Section 1:</h5> An auction will occur at Winter Meetings consisting of players coming off of contract. All owners present or via satellite will have the opportunity to bid on a player in the hopes of winning his services for the upcoming season. Owners participating in the auction will nominate a player that they wish to bid on, once nominated that constitutes a bid of $30 for that player even if there are no other bids. The nominations will proceed in order determined by picking names out of a hat. 

<h5>Section 2:</h5> There shall be an auctioneer, and he shall determine the a fair "below market" opening bid for a player based on intuition. If there are no takers the bid shall be dropped until a bid is placed down to $30. If no bids are placed he is awarded to the owner that nominated the player for $30.

<h5>Section 3:</h5> Every player eligible for auction will be called. Random nominations will proceed for full rounds only, after that the auctioneer will call the remaining players. If there is no bid for an auctioneer called player the original owner has the option to keep this player for $30 but is not obligated to do so. ex. 12 owners in the auction and 30 players. 2 full rounds of nominations, 6 players called by auctioneer.

<h5>Section 4:</h5> Since a nomination constitutes a bid, an owner may decide to skip their turn and therefore not be on the hook if no one else bids.

<h5>Section 5:</h5> The original owner has the option to retain matching rights on the player once  the bidding has stopped and the auction has closed. The auctioneer will ask the original owner if they want to match the bid and retain the player. 

<h5>Section 6:</h5> Once bidding has stopped, and before the original owner decides if they will exercise their matching right option (see section 5), the team with the last highest bid will have one more opportunity to up their final bid with a "Last Chance" bid, which can stay the same as their final bid, or increased in an effort to secure the player.  If the original owner exercises their matching rights on the new "Last Chance" bid, then the player goes back to the original owner, and the auction process is closed for that player. 

<h5>Section 7:</h5> Auctioned players are considered drafted with their fee for the upcoming year paid at auction and are therefore eligible to be kept in the following offseason for 1 or 2 additional years at the value determined at rankings.


<a id='a8'></a><h4>Article Eight: The Winter Meetings</h4>

The fruits of democracy do not blossom without labor and dedication.  Each Winter, we will brave the elements and convene in an isolated place in order to better our league. This is truly a regrettable sacrifice.  At least that's what we tell our wives, girlfriends and employers when they ask why we have to go out East for the weekend.  

<h5>Section 1:</h5> The Winter Meetings are held in order to address rule changes of any kind.  The goal of these Meetings is to leave no issue unresolved.  Any new votes, proposals or re-votes on past decisions should be brought up.

<h5>Section 2:</h5> The draft date will be decided during the Meetings.
  
<h5>Section 3:</h5> The Meetings should be scheduled to coincide with NFL playoff football.  Also, we should gamble on the games and play poker, too.

<h5>Section 4:</h5> At the Winter Meetings, Matt Greenberg will make a keynote address to the league.

<h5>Section 5:</h5> The auction will be held on the Saturday of Winter Meetings.

<h5>Section 6:</h5> Matt Seltzer will attempt to sabotage the weekend with the help of too much alcohol and no one who cares enough to keep him in check. Landlords of the East be warned.

<h5>Section 7:</h5> By the end of the 2012 season, each owner will contribute $250 for the security deposit for the 2013 season. This should be a one time contribution and carry forward for the 2014 season unless Seltzer does something stupid (see Section 6).

<h5>Section 8:</h5> Though wildly unpopular, Siporin will revisit the idea of defense and or errors counting for scoring purposes.

<h5>Section 9:</h5> Mason Hoffman can't come.


<a id='a9'></a><h4>Article Nine: Rosters</h4>

<h5>Section 1:</h5> It is in everyone's best interest if all owners field a starting lineup that is full of actual real life major league baseball players. You must start 1 of each of the following batter position players: Catcher, 1B, 2B, SS, 3B.  You must start 3 OF.  You must start 1 Flex DH (which can be any of the aforementioned position player positions), totaling 9 batters in your active lineup.  You must start 4 SP and 1 RP.  You must also start 1 flex Pitcher, totaling 6 Pitchers in your active lineup (15 active batters + pitchers in your starting lineup)

<h5>Section 2:</h5> Your bench will consist of a total of 5 spots that can be filled by any type of player.

<h5>Section 3:</h5> In addition to the 5 bench spots, there will be 3 slots available for other player types (Injured, Inactive, Suspended, Minor Leaguer, etc.)  Of the 3 spots, 1 is designated for a Minor Leaguer, and the other 2 spots are to be considered Flex spots.  Max minor leaguers is 3. Max injured players is 2.

<h5>Section 4:</h5> In the event that a starting pitcher is announced as injured or scratched after lineups have locked and before they have actually pitched for the week, the team will have an opportunity to exercise the Injury/Scratch protection clause allowing a starting pitcher swap. This swap will include the injured/scratched player from the active lineup to the bench, and a starting pitcher from the team's bench to the active lineup.  This swap can only include a starting pitcher that is already on the team's bench for that week (you can not add a free agent to swap in), and that has not already made his scheduled start for the week.  This Injury/Scratch protection clause can only be exercised up to two times a season, including the playoffs.  


<a id='a10'></a><h4>Article Ten: The Offseason and Misc items</h4>

<h5>Section 1:</h5> Rosters freeze once rankings begin, unfreeze once keepers values are announced, refreeze a reasonable amount of time before keeper night and re unfreeze once envelopes are unveiled at keeper night.

<h5>Section 2:</h5> Until decided otherwise the keeper party will be at Siporins house.

2014: Astoria keeper night, bitches!

<h5>Section 3:</h5> Rotiss.com was born in 2008 and maintained in an ongoing capacity by Kurt. Non cbs league matters will be housed there if possible.

<h5>Section 4:</h5> Until unseated, Mike Board and Matt Hennings are reigning beer pong champions having won 11 straight games and 12 of 14 at the meetings of 2010. 

<h5>Section 5:</h5> Much like George Washington before him Glenn Weinberg voluntarily ends his unquestioned reign as league commissioner in 2011 as a way for the league to prosper without the possibility of having a ruler for life. After much chanting of his name and discomfort without the only leader this league has ever known, Novick and Henno accept co-comish duties.

<h5>Section 6:</h5>  Real money can not be included as part of a St. Pete's trade, nor can you gamble in real life with St. Pete's Brognas.

<br/><br/>
<div class='center'><strong>Signed,
The owners of St. Petersburg Keepatorium<br/>
Drafted in the frigid Winter of ought seven.<br/>
Amended in the balmy Spring of 2012<br/>
Amended again in the muggy Summer of 2014<br/>
</strong>
</div>

<br/>
<p>
Please Note: All other league rules and information (including scoring, roster limits, transaction format, etc...) are listed in the "Rules" page under the "League Home" tab.  
    </p>
  </div>
</div>	

<?php

  // Display footer
  LayoutUtil::displayFooter();
?>

</body>
</html>