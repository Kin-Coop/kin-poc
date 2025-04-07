biz.jmaconsulting.customcontribstatuses version 1.2 for CiviCRM 4.7
===================================================================

Custom Contribution Statuses allows users to switch back and forth between core defined Contribution Statuses and custom Contribution Statuses. Without this extension, CiviCRM by default prevents switching from custom contribution statuses in an attempt to avoid accounting data corruption, since it doesn't know what entries should be posted. This extension helps organization with specialized support for non-core accounting processes.

Background

CiviCRM has a number of default statuses for Contributions: Completed, Pending, Cancelled, Failed, and so on. Changes between statuses are used to make appropriate accounting entries. Once transitions between statuses began causing the creation of relevant accounting entries in CiviCRM 3, it became important to protect the list of options from someone thinking they could remove, say, Completed, and insert Paid. So it's no longer possible to add additional items via Administer > System Settings > Option Groups, and then clicking Options beside Contribution Status. Some organizations nonetheless want to use custom Contribution Statuses; perhaps they don't export accounting entries, or they have custom code to support other types of accounting transactions. This extension facilitates the use of these custom contribution statuses while editting contributions.

Installation instructions for Custom Contribution Status
===========================================================

* Setup Extensions Directory 
  * If you have not already done so, go to Administer >> System Settings >> Directories
    * Set an appropriate value for CiviCRM Extensions Directory. For example, for Drupal, /path/to/drupalroot/sites/all/modules/Extensions/
    * In a different window, ensure the directory exists and is readable by your web server process.
  * Click Save.
* Setup Extensions Resources URL
  * If you have not already done so, go to Administer >> System Settings >> Resource URLs
    * Beside Extension Resource URL, enter an appropriate values such as http://yourorg.org/sites/all/modules/Extensions/
  * Click Save.
* Enable the Extension
  * Navigate to Administer > System Settings > Extensions.
  * Click Add New tab.
  * Click Download and Install button.
