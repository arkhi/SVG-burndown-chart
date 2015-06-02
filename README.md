This project provides a fast "database free" way to update a burndown chart

The PHP could of course use classes and the like, this is far from clean.
Feel free to improve by forking. :)


Usage
=====

* Every day, you can update a file `data.php` to reflect points burned, User Story points removed and bugs fixing time;
* If you don't want to differentiate points and User Story points, you can remove things related to User Story points;
* If the sprint is unusualy long, the legends might not look really good. You can add the class `long_sprint` to "#grid".



Variables
=========

All variables are defined in the $sprint array.
* **number**
  is the ID number of the current sprint (could be called sprintID);
* **days**
  is the amount of days in the sprint;
* **points**
  is the total number of points for Tasks commited to during the sprint;
* **USPoints**
  is the total number of points for User Stories in the sprint;
* **dailyPoints**
  is an array to be updated daily, based on the number of tasks points burned in the previous day;
* **dailyUSPoints**
  is an array to be updated daily, based on the number of points for User Stories finished the previous day;
* **dailyBugs**
  is an array to be updated daily, based on the time spent on bugs during the previous day.



Projections
===========

Two projections represent different estimations for the sprint success:
* **global projection**
  is based on the average points burned since the start of the sprint;
* **a local projection**
  is based on the points burned during the last day.



URL parameters
==============

`type` will show different versions of the sprint:

* **success**
  Shows how much points would have made the sprint successful.
* **added_hours**
  Shows the cumulative hours spent on bug fixing and burned hours. This can explain how much work was globally done, even if the sprint is unsuccessful.

`datapath` defines a path where to look for a custom data.php.

Embed view (Work in progress)
=============================
   burndown_chart_embed.php shows how a table could be used by the SVG with JS. You'll need to update the header of the SVG file for this to work. (Just add "/*" where necessary)
