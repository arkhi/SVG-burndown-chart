This project provides a fast "database free" way to update a burndown chart

The PHP could of course use classes and the like, this is far from clean.
Feel free to improve by forking. :)


Usage
=====

* Every day, you can update the file data.php to reflect points burned, User Story points removed and bugs fixing time;
* If you don't want to differentiate points and User Story points, you can remove things related to User Story points;
* If the sprint is unusualy long, the legends might not look really good. You can add the class ".long_sprint" to the "#grid" element.
* Since you might have multiple sprints, you can move "common.css" to an other folder and refer to it accordingly to keep styles consistent whatever modifications are made on the CSS.



Variables
=========

All variables are defined in the $sprint array.
* *number*
  number of the current sprint (could be called sprintID);
* *days*
  amount of days in the sprint;
* *points*
  total number of points for Tasks commited to during the sprint;
* *USPoints*
  total number of points for User Stories in the sprint;
* *dailyPoints*
  array to be updated daily, based on the number of tasks points burned in the previous day;
* *dailyUSPoints*
  array to be updated daily, based on the number of points for User Stories finished the previous day;
* *dailyBugs*
  array to be updated daily, based on the time spent on bugs during the previous day.



Projections
===========

Two projections are are available:
* *a global projection*
  sprint success estimation based on the average points burned since the start of the sprint;
* *a local projection*
  sprint success estimation based on the points burned during the last day.



Alternate views
===============

* *burndown_chart_success.php*
  shows the sprint as if it was successful.
* *burndown_chart_added_hours.php*
  shows the cumulative hours spent on bug fixing and burned hours. This can explain how much work was globally done, even if the sprint is unsuccessful.
* *burndown_chart_embed.php*
  work in progress to show how a table could be used by the SVG with JS. You'll need to update the header of the SVG file for this to work. (Just add "/*" where necessary)