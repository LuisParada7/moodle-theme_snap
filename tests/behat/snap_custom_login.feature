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
# along with Moodle.  If not, see <http://www.gnu.org/licenses/>.
#
# Tests for visibility of activity restriction tags.
#
# @package    theme_snap
# @copyright Copyright (c) 2018 Blackboard Inc.
# @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later


@theme @theme_snap @theme_snap_login @axe
Feature: When the moodle theme is set to Snap, the custom snap login form should be shown.

  @javascript
  Scenario: The login template must contain the custom snap form.
    Given I am on login page
    And I check element "#login" has class "snap-custom-form"


  @javascript
  Scenario: Login page must be accessible
    Given I am on login page
    Then I should not see any accessibility violations

