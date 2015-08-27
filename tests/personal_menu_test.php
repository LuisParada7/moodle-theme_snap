<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Local Tests
 *
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_snap\tests;

use theme_snap\local;

defined('MOODLE_INTERNAL') || die();

/**
 * @package   theme_snap
 * @copyright Copyright (c) 2015 Moodlerooms Inc. (http://www.moodlerooms.com)
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class theme_snap_personal_menu_test extends \advanced_testcase {

    protected $user1;
    protected $user2;
    protected $teacher;
    protected $course1;
    protected $course2;
    protected $groupA;
    protected $groupB;
    
    public function setUp() {
        global $CFG, $DB;
        
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();


        require_once($CFG->dirroot . '/mod/forum/lib.php');

        $this->resetAfterTest();

        $this->course1 = $this->getDataGenerator()->create_course();
        $this->course2 = $this->getDataGenerator()->create_course();

        $this->user1 = $this->getDataGenerator()->create_user();
        $this->user2 = $this->getDataGenerator()->create_user();
        $this->teacher = $this->getDataGenerator()->create_user();

        // Enrol (as students) user1 to both courses but user2 only to course2.
        $sturole = $DB->get_record('role', array('shortname'=>'student'));
        $this->getDataGenerator()->enrol_user($this->user1->id,
            $this->course1->id,
            $sturole->id);
        $this->getDataGenerator()->enrol_user($this->user1->id,
            $this->course2->id,
            $sturole->id);
        $this->getDataGenerator()->enrol_user($this->user2->id,
            $this->course2->id,
            $sturole->id);

        // Enrol teacher on both courses.
        $teacherrole = $DB->get_record('role', array('shortname'=>'teacher'));
        $this->getDataGenerator()->enrol_user($this->teacher->id,
            $this->course1->id,
            $teacherrole->id);
        $this->getDataGenerator()->enrol_user($this->teacher->id,
            $this->course2->id,
            $teacherrole->id);

        // Add 2 groups to course2.
        $this->groupA = $this->getDataGenerator()->create_group([
            'courseid' => $this->course2->id,
            'name' => 'A'
        ]);
        $this->groupB = $this->getDataGenerator()->create_group([
            'courseid' => $this->course2->id,
            'name' => 'B'
        ]);

        // Add user1 to both groups but user2 to just groupA.
        groups_add_member($this->groupA->id, $this->user1);
        groups_add_member($this->groupB->id, $this->user1);
        groups_add_member($this->groupA->id, $this->user2);

    }

    public function tearDown() {
        // We must clear the subscription caches. This has to be done both before each test, and after in case of other.
        // tests using these functions.
        \mod_forum\subscriptions::reset_forum_cache();
    }

    /**
     *
     * @throws \coding_exception
     */
    public function test_forums() {

        $this->do_forum_type('forum');
        $this->do_forum_type('hsuforum', 11, 7);

    }

    /**
     * Create a discussion.
     *
     * @param $ftype
     * @param $courseid
     * @param $userid
     * @param $forumid
     * @return mixed
     * @throws \coding_exception
     */
    protected function create_discussion($ftype, $courseid, $userid, $forumid, $groupid = null) {
        // Add discussion to course 1 started by user1.
        $record = new \stdClass();
        $record->course = $courseid;
        $record->userid = $userid;
        $record->forum = $forumid;
        if ($groupid !== null) {
            $record->groupid = $groupid;
        }
        return ($this->getDataGenerator()->get_plugin_generator('mod_'.$ftype)->create_discussion($record));
    }

    /**
     * Create a post.
     *
     * @param $ftype
     * @param $courseid
     * @param $userid
     * @param $forumid
     * @param $discussionid
     * @return mixed
     * @throws \coding_exception
     */
    protected function create_post($ftype, $courseid, $userid, $forumid, $discussionid){
        $record = new \stdClass();
        $record->course = $courseid;
        $record->userid = $userid;
        $record->forum = $forumid;
        $record->discussion = $discussionid;
        return ($this->getDataGenerator()->get_plugin_generator('mod_'.$ftype)->create_post($record));
    }

    /**
     * @param string $ftype - forum or hsuforum
     * @param int $u1offset
     * @param int $u2offset
     * @throws \coding_exception
     */
    protected function do_forum_type($ftype, $u1offset =0, $u2offset = 0) {

        if ($u1offset === 0 && $u2offset ===0) {
            // There are no forums to start with, check activity array is empty.
            $activity = local::recent_forum_activity($this->user1->id);
            $this->assertEmpty($activity);
        }

        // Create 2 regular forums, one in each course.
        $record = new \stdClass();
        $record->course = $this->course1->id;
        $forum1 = $this->getDataGenerator()->create_module($ftype, $record);

        $record = new \stdClass();
        $record->course = $this->course2->id;
        $forum2 = $this->getDataGenerator()->create_module($ftype, $record);

        // Create a date restricted forum - won't be available to students until one week from now.
        $record = new \stdClass();
        $record->course = $this->course2->id;
        $opts = ['availability'] = '{"op":"&","c":[{"type":"date","d":">=","t":'.(time()+WEEKSECS).'}],"showc":[true]}';
        $forum3 = $this->getDataGenerator()->create_module($ftype, $record, $opts);

        // Add discussion to course 1 started by user1.
        $discussion1 = $this->create_discussion($ftype, $this->course1->id, $this->user1->id, $forum1->id);

        // Make sure teacher & user1 has a count of 1 post and user2 has a count of 0 posts
        $activity = local::recent_forum_activity($this->teacher->id);
        // Should be 1 post.
        $this->assertEquals($u1offset+1, count($activity));
        $activity = local::recent_forum_activity($this->user1->id);
        // Should be 1 post.
        $this->assertEquals($u1offset+1, count($activity));
        $activity = local::recent_forum_activity($this->user2->id);
        // Should be 0 posts.
        $this->assertEquals($u1offset, count($activity));

        // Add discussion to course 2 started by user1.
        $discussion2 = $this->create_discussion($ftype, $this->course2->id, $this->user1->id, $forum2->id);

        // Add discussions to course2 started by user2.
        $discussion3 = $this->create_discussion($ftype, $this->course2->id, $this->user2->id, $forum2->id);

        // Add post to forum1 and 2 by user1 and user2.
        $this->create_post($ftype, $this->course1->id, $this->user1->id, $forum1->id, $discussion1->id);
        $this->create_post($ftype, $this->course2->id, $this->user2->id, $forum2->id, $discussion2->id);

        // Add post to forum2 by user1.
        $this->create_post($ftype, $this->course2->id, $this->user2->id, $forum2->id, $discussion2->id);

        // Note: In testing number of posts, discussions are counted too as there is a post for each discussion created.

        // Test user1 viewable posts.
        $activity = local::recent_forum_activity($this->user1->id);
        // Should be 6 posts.
        $this->assertEquals($u1offset+6, count($activity));

        // Test user2 viewable posts.
        $activity = local::recent_forum_activity($this->user2->id);
        // Should be 4 posts - user2 is not enrolled on course1.
        $this->assertEquals($u2offset+4, count($activity));

        // Create a forum with group mode enabled.
        $record = new \stdClass();
        $record->course = $this->course2->id;
        $forum4 = $this->getDataGenerator()->create_module($ftype, $record, ['groupmode' => SEPARATEGROUPS]);

        // Add a discussion and 2 posts for groupA users.
        $discussion4 = $this->create_discussion($ftype,
            $this->course2->id, $this->user1->id, $forum4->id,  $this->groupA->id);

        // (At this point - 7 posts for user1, 5 for user2).

        for ($p=1; $p<=2; $p++) {
            // Create 1 post by user1 and user2.
            $user = $p==1 ? $this->user1 : $this->user2;
            $this->create_post($ftype, $this->course2->id, $user->id, $forum4->id, $discussion4->id);
        }

        // (At this point - 9 posts for user1, 7 for user2).

        // Add a discussion and 1 post for groupB users.
        $discussion5 = $this->create_discussion($ftype,
            $this->course2->id, $this->user1->id, $forum4->id,  $this->groupB->id);
        $this->create_post($ftype, $this->course2->id, $this->user1->id, $forum4->id, $discussion5->id);

        // (At this point - 11 posts for user1, 7 for user2).

        // Check user1.
        $activity = local::recent_forum_activity($this->user1->id);
        // Should be offset + 11 posts.
        $this->assertEquals($u1offset+11, count($activity));

        // Check user2.
        $activity = local::recent_forum_activity($this->user2->id);
        // Should be offset + 7 posts.
        $this->assertEquals($u2offset+7, count($activity));
    }
}