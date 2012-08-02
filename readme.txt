This plugin provides a fast database free way to update a burndown chart

A few key principles when working with it.



*** git branches ***

* "master" branch is mainly used for SVG changes;
* "develop" branch is for the atempt to link this chart to a database (WIP);
* Feel free to add as many branches as you want!



*** usage ***

* Every day, you can update the file data.php to reflect points burned,
  User Story points removed and bugs fixing time;
* If you don't want to differentiate points and User Story points,
  you can remove things related to User Story points;
* If the sprint is unusualy long, the legends might not look really good.
  You can add the class ".long_sprint" to the "#grid" element.



*** alternate views ***

* "burndown_chart_success.php" shows the sprint as if it was successful.
  As this is purely informative and cheats on real burndown,
  you need to overwrite the following variables:
** $sprint = ['points']
** $sprint = ['USPoints']
* "burndown_chart_added_hours.php" shows the cumulative hours spent on bug fixing and burned hours.
  This can explain how much work was globally done, even if the sprint is unsuccessful.
* "burndown_chart_embed.php" is an atempt to show how a table could be used by the SVG with JS.
  You'll need to update the header of the SVG file for this to work. (Just add "/*" where necessary)
