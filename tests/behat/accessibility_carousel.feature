# This file is part of Moodle - http://moodle.org/
#
# Moodle is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
#
# Moodle is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with Moodle. If not, see <http://www.gnu.org/licenses/>.
#
# Test for Snap's carousel accessibility
#
# @package    theme_snap
# @autor      Rafael Becerra
# @copyright  Copyright (c) 2022 Open LMS (https://www.openlms.net)
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later

@theme @theme_snap @theme_snap_ax
Feature: Snap's carousel must have the correct attributes to make it accessible.

  Background:
    Given the following config values are set as admin:
      | slide_one_title     | Title for slide one | theme_snap |
      | slide_two_title     | Title for slide two | theme_snap |
      | cover_carousel      | 1                   | theme_snap |
    And I log in as "admin"
    And I close the personal menu
    And I follow "Manage private files..."
    And I upload "lib/tests/fixtures/gd-logo.png" file to "Files" filemanager
    And I click on "Save changes" "button"
    And I log out

  @javascript @_file_upload
  Scenario: Snap's carousel must comply with the accessibility standards.
    Given I skip because "It will be improved in INT-18324"
    Given I am using Open LMS
    And I log in as "admin"
    And the following config values are set as admin:
      | linkadmincategories | 0 |
    And I am on site homepage
    And I click on "#admin-menu-trigger" "css_element"
    And I expand "Site administration" node
    And I expand "Appearance" node
    And I expand "Themes" node
    And I follow "Snap"
    And I click on "form#adminsettings div.settingsform div.row ul#snap-admin-tabs li:nth-child(2)" "css_element"
    And I click on "#themesnapcoverdisplay #admin-slide_one_image div[id^='filemanager-'] .filemanager-container .dndupload-message .dndupload-arrow" "css_element"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    And I click on "gd-logo.png" "link"
    And I click on "Select this file" "button"
    And I click on "Save changes" "button"
    # Check the existence of the carousel in the front page.
    And I am on front page
    And I should see "Title for slide one"
    # Play and pause buttons should not be visible when only one slide exists.
    Then "#carousel-play-resume-buttons #play-button" "css_element" should not be visible
    Then "#carousel-play-resume-buttons #pause-button" "css_element" should not be visible
    And I click on "#admin-menu-trigger" "css_element"
    And I expand "Site administration" node
    And I expand "Appearance" node
    And I expand "Themes" node
    And I follow "Snap"
    And I click on "form#adminsettings div.settingsform div.row ul#snap-admin-tabs li:nth-child(2)" "css_element"
    And I click on "#themesnapcoverdisplay #admin-slide_two_image div[id^='filemanager-'] .filemanager-container .dndupload-message .dndupload-arrow" "css_element"
    And I click on "Private files" "link" in the ".fp-repo-area" "css_element"
    # Since this is for testing purposes only, it doesn't matter that the images are the same.
    And I click on "a.fp-file" "css_element"
    And I click on "Select this file" "button"
    And I click on "Save changes" "button"
    And I am on front page
    # Check that after adding a second slide, it will change automatically.
    And I should see "Title for slide one"
    And I wait "10" seconds
    And I should see "Title for slide two"
    And the "aria-label" attribute of "#snap-site-carousel .carousel-indicators button[data-slide-to='0']" "css_element" should contain "slide-0"
    And the "aria-label" attribute of "#snap-site-carousel .carousel-indicators button[data-slide-to='1']" "css_element" should contain "slide-1"
    And the "aria-current" attribute of "#snap-site-carousel .carousel-indicators button:not(.active)" "css_element" should contain "false"
    And the "aria-current" attribute of "#snap-site-carousel .carousel-indicators button.active" "css_element" should contain "true"
    Then "#carousel-play-resume-buttons #play-button" "css_element" should exist
    Then "#carousel-play-resume-buttons #pause-button" "css_element" should exist
    And I click on "#carousel-play-resume-buttons #pause-button" "css_element"
    And I wait "10" seconds
    # Check that the pause button is working correctly and is not going to the second slide after clicking it.
    And I should not see "Title for slide two"
    # Resume of the auto rotation slides to check the functionality of the play button.
    And I click on "#carousel-play-resume-buttons #play-button" "css_element"
    And I wait "10" seconds
    And I should see "Title for slide two"