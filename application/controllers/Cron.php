<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Cron extends CI_Controller
{

    function __construct()
    {
        parent::__construct();

        $this->output->enable_profiler(FALSE);

        //Example: /usr/bin/php /home/ubuntu/mench-web-app/index.php cron save_profile_pic
    }


    //Cache of cron jobs as of now [keep in sync when updating cron file]
    //* * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron message_file_save
    //* * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron message_fb_sync_attachments
    //*/5 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron message_drip
    //*/6 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron save_profile_pic
    //31 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron intent_sync
    //45 * * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron student_reminder_complete_task
    //30 2 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron algolia_sync b 0
    //30 4 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron algolia_sync u 0
    //30 3 * * * /usr/bin/php /home/ubuntu/mench-web-app/index.php cron e_score_recursive


    function show_missing_us()
    {
        $q = $this->db->query("SELECT DISTINCT(ur_child_u_id) as p_id FROM tb_entity_links ur WHERE NOT EXISTS (
   SELECT 1
   FROM   tb_entities u
   WHERE  u_id = ur_child_u_id
   );");

        $results = $q->result_array();

        echo_json($results);
    }

    function intent_sync($c_id = 7240, $update_c_table = 1)
    {
        //Cron Settings: 31 * * * *
        //Syncs intents with latest caching data:
        $sync = $this->Db_model->c_recursive_fetch($c_id, true, $update_c_table);
        if (isset($_GET['redirect']) && strlen($_GET['redirect']) > 0) {
            //Now redirect;
            header('Location: ' . $_GET['redirect']);
        } else {
            //Show json:
            echo_json($sync);
        }
    }


    function algolia_sync($obj, $obj_id = 0)
    {
        echo_json($this->Db_model->algolia_sync($obj, $obj_id));
    }

    function list_duplicate_cs()
    {

        $q = $this->db->query('select c1.* from tb_intents c1 where (select count(*) from tb_intents c2 where c2.c_outcome = c1.c_outcome) > 1 ORDER BY c1.c_outcome ASC');
        $duplicates = $q->result_array();


        $prev_title = null;
        foreach ($duplicates as $c) {
            if ($prev_title != $c['c_outcome']) {
                echo '<hr />';
                $prev_title = $c['c_outcome'];
            }

            echo '<a href="/intents/' . $c['c_id'] . '">#' . $c['c_id'] . '</a> ' . $c['c_outcome'] . '<br />';
        }
    }

    function list_duplicate_us()
    {

        $q = $this->db->query('select u1.* from tb_entities u1 where (select count(*) from tb_entities u2 where u2.u_full_name = u1.u_full_name) > 1 ORDER BY u1.u_full_name ASC');
        $duplicates = $q->result_array();


        $prev_title = null;
        foreach ($duplicates as $u) {
            if ($prev_title != $u['u_full_name']) {
                echo '<hr />';
                $prev_title = $u['u_full_name'];
            }

            echo '<a href="/entities/' . $u['u_id'] . '">#' . $u['u_id'] . '</a> ' . $u['u_full_name'] . '<br />';
        }
    }


    function e_score_recursive($u = array())
    {

        //Updates u__e_score based on number/value of connections to other intents/entities
        //Cron Settings: 2 * * * 30

        //Define weights:
        $score_weights = array(
            'u__childrens' => 0, //Child entities are just containers, no score on the link

            'tr_en_child_id' => 1, //Engagement initiator
            'tr_en_creator_id' => 1, //Engagement recipient

            'x_parent_u_id' => 5, //URL Creator
            'x_u_id' => 8, //URL Referenced to them

            'w_child_u_id' => 13, //Subscriptions
            'c_parent_u_id' => 21, //Active Intents
        );

        //Fetch child entities:
        $entities = $this->Db_model->en_children_fetch(array(
            'ur_parent_u_id' => (count($u) > 0 ? $u['u_id'] : $this->config->item('primary_en_id')),
            'ur_status >=' => 0, //Pending or Active
            'u_status >=' => 0, //Pending or Active
        ));

        //Recursively loops through child entities:
        $score = 0;
        foreach ($entities as $u_child) {
            //Addup all child sores:
            $score += $this->e_score_recursive($u_child);
        }

        //Anything to update?
        if (count($u) > 0) {

            //Update this row:
            $score += count($entities) * $score_weights['u__childrens'];

            $score += count($this->Db_model->tr_fetch(array(
                    'tr_en_child_id' => $u['u_id'],
                ), 5000)) * $score_weights['tr_en_child_id'];
            $score += count($this->Db_model->tr_fetch(array(
                    'tr_en_creator_id' => $u['u_id'],
                ), 5000)) * $score_weights['tr_en_creator_id'];

            $score += count($this->Db_model->x_fetch(array(
                    'x_status >' => -2,
                    'x_u_id' => $u['u_id'],
                ))) * $score_weights['x_u_id'];
            $score += count($this->Db_model->x_fetch(array(
                    'x_status >' => -2,
                    'x_parent_u_id' => $u['u_id'],
                ))) * $score_weights['x_parent_u_id'];

            $score += count($this->Db_model->in_fetch(array(
                    'c_parent_u_id' => $u['u_id'],
                ))) * $score_weights['c_parent_u_id'];
            $score += count($this->Db_model->w_fetch(array(
                    'w_child_u_id' => $u['u_id'],
                ))) * $score_weights['w_child_u_id'];

            //Update the score:
            $this->Db_model->u_update($u['u_id'], array(
                'u__e_score' => $score,
            ));

            //return the score:
            return $score;

        }
    }


    function message_drip()
    {

        exit; //Logic needs updating

        //Cron Settings: */5 * * * *

        //Fetch pending drips
        $e_pending = $this->Db_model->tr_fetch(array(
            'tr_status' => 0, //Pending work
            'tr_en_type_id' => 4281, //Scheduled Drip
            'tr_timestamp <=' => date("Y-m-d H:i:s"), //Message is due
            //Some standard checks to make sure, these should all be true:
            'tr_en_child_id >' => 0,
            'tr_in_child_id >' => 0,
        ), 200);


        //Lock item so other Cron jobs don't pick this up:
        lock_cron_for_processing($e_pending);


        $drip_sent = 0;
        foreach ($e_pending as $tr_content) {

            //Fetch user data:
            $ws = $this->Db_model->w_fetch(array());

            if (count($ws) > 0) {

                //Prepare variables:
                $json_data = unserialize($tr_content['tr_metadata']);

                //Send this message:
                $this->Comm_model->send_message(array(
                    array_merge($json_data['i'], array(
                        'tr_en_child_id' => $ws[0]['u_id'],
                        'i_c_id' => $json_data['i']['i_c_id'],
                    )),
                ));

                //Update Engagement:
                $this->Db_model->tr_update($tr_content['tr_id'], array(
                    'tr_status' => 2, //Publish
                ));

                //Increase counter:
                $drip_sent++;
            }
        }

        //Echo message for cron job:
        echo $drip_sent . ' Drip messages sent';

    }


    function en_metadata()
    {
        //A function that creates a cache array to be saved in config for country/language/time-zone conversion into entities
        $fetch = array(
            'en_countries' => 3089,
            'en_languages' => 3287,
            'en_timezones' => 3289,
            'en_gender' => 3290,
        );
        echo '//Copy/paste this into the config.php file and replace with old variable:<br /><br />';
        echo '$config[\'en_metadata\'] = array(<br />';
        foreach ($fetch as $array_key => $en_id) {
            //Fetch all children for this entity:
            $entities = $this->Db_model->en_children_fetch(array(
                'ur_parent_u_id' => $en_id,
                'ur_status >=' => 0, //Pending or Active
                'u_status >=' => 0, //Pending or Active
            ));
            echo '&nbsp;&nbsp;&nbsp;\'' . $array_key . '\' => array(<br />';
            foreach ($entities as $en) {
                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;\'' . strtolower($en['ur_notes']) . '\' => ' . $en['ur_child_u_id'] . ',<br />';
            }
            echo '&nbsp;&nbsp;&nbsp;),<br />';
        }

        //Also add gender:
        echo ');';
    }

    function save_profile_pic()
    {


        $max_per_batch = 20; //Max number of scans per run

        $e_pending = $this->Db_model->tr_fetch(array(
            'tr_status' => 0, //Pending
            'tr_en_type_id' => 4299, //Save media file to Mench cloud
        ), $max_per_batch);


        //Lock item so other Cron jobs don't pick this up:
        lock_cron_for_processing($e_pending);


        $counter = 0;
        foreach ($e_pending as $u) {

            //Check URL and validate:
            $error_message = null;
            $curl = curl_html($u['tr_content'], true);

            if (!$curl) {
                $error_message = 'Invalid URL (start with http:// or https://)';
            } elseif ($curl['x_type'] != 4) {
                $error_message = 'URL [Type ' . $curl['x_type'] . '] Does not point to an image';
            }

            if (!$error_message) {

                //Save the file to S3
                $new_file_url = save_file($u['tr_content'], $u);

                if (!$new_file_url) {
                    $error_message = 'Failed to upload the file to Mench CDN';
                }

                //Check to make sure this is not a Generic FB URL:
                foreach (array(
                             'ecd274930db69ba4b2d9137949026300',
                             '5bf2d884209d168608b02f3d0850210d',
                             'b3575aa3d0a67fb7d7a076198b442b93',
                             'e35cf96f814f6509d8a202efbda18d3c',
                             '5d2524cb2bdd09422832fa2d25399049',
                             '164c8275278f05c770418258313fb4f4',
                             '',
                         ) as $generic_url) {
                    if (substr_count($new_file_url, $generic_url) > 0) {
                        //This is the hashkey for the Facebook Generic User icon:
                        $error_message = 'This is the user generic icon on Facebook';
                        break;
                    }
                }

                if (!$error_message) {

                    //Save URL:
                    $new_x = $this->Db_model->x_create(array(
                        'x_parent_u_id' => $u['u_id'],
                        'x_u_id' => $u['u_id'],
                        'x_url' => $new_file_url,
                        'x_clean_url' => $new_file_url,
                        'x_type' => 4, //Image
                        'x_status' => 1, //Published
                    ));

                    //Replace cover photo only if this user has no cover photo set:
                    if (!(intval($u['u_cover_x_id']) > 0)) {

                        //Update Cover ID:
                        $this->Db_model->u_update($u['u_id'], array(
                            'u_cover_x_id' => $new_x['x_id'],
                        ));

                        //Log engagement:
                        $this->Db_model->tr_create(array(
                            'tr_en_creator_id' => $u['u_id'],
                            'tr_en_child_id' => $u['u_id'],
                            'tr_en_type_id' => 4263, //Entity Modified
                            'tr_content' => 'Profile cover photo updates from Facebook Image [' . $u['tr_content'] . '] to Mench CDN [' . $new_file_url . ']',
                            'e_x_id' => $new_x['x_id'],
                        ));
                    }
                }
            }

            //Update engagement:
            $this->Db_model->tr_update($u['tr_id'], array(
                'tr_content' => ($error_message ? 'ERROR: ' . $error_message : 'Success') . ' (Original Image URL: ' . $u['tr_content'] . ')',
                'tr_status' => 2, //Publish
            ));

        }

        echo_json($e_pending);
    }

    function message_file_save()
    {

        //Cron Settings: * * * * *

        /*
         * This cron job looks for all engagements with Facebook attachments
         * that are pending upload (i.e. tr_status=0) and uploads their
         * attachments to amazon S3 and then changes status to Published
         *
         */

        $max_per_batch = 10; //Max number of scans per run

        $e_pending = $this->Db_model->tr_fetch(array(
            'tr_status' => 0, //Pending file upload to S3
            'tr_en_type_id IN (4277,4280)' => null, //Sent/Received messages
        ), $max_per_batch);


        //Lock item so other Cron jobs don't pick this up:
        lock_cron_for_processing($e_pending);


        $counter = 0;
        foreach ($e_pending as $ep) {

            //Prepare variables:
            $json_data = unserialize($ep['tr_metadata']);

            //Loop through entries:
            if (is_array($json_data) && isset($json_data['entry']) && count($json_data['entry']) > 0) {
                foreach ($json_data['entry'] as $entry) {
                    //loop though the messages:
                    foreach ($entry['messaging'] as $im) {
                        //This should only be a message
                        if (isset($im['message'])) {
                            //This should be here
                            if (isset($im['message']['attachments'])) {
                                //We should have attachments:
                                foreach ($im['message']['attachments'] as $att) {
                                    //This one too! It should be one of these:
                                    if (in_array($att['type'], array('image', 'audio', 'video', 'file'))) {

                                        //Store to local DB:
                                        $new_file_url = save_file($att['payload']['url'], $json_data);

                                        //Update engagement data:
                                        $this->Db_model->tr_update($ep['tr_id'], array(
                                            'tr_content' => (strlen($ep['tr_content']) > 0 ? $ep['tr_content'] . "\n\n" : '') . '/attach ' . $att['type'] . ':' . $new_file_url, //Makes the file preview available on the message
                                            'tr_status' => 2, //Mark as done
                                        ));

                                        //Increase counter:
                                        $counter++;
                                    }
                                }
                            }
                        }
                    }
                }
            } else {
                //This should not happen, report:
                $this->Db_model->tr_create(array(
                    'tr_content' => 'cron/bot_save_files() fetched tr_metadata() that was missing its [entry] value',
                    'tr_metadata' => $json_data,
                    'tr_en_type_id' => 4246, //System Error
                ));
            }

            if ($counter >= $max_per_batch) {
                break; //done for now
            }
        }
        //Echo message for cron job:
        echo $counter . ' Incoming Messenger file' . ($counter == 1 ? '' : 's') . ' saved to Mench cloud.';
    }

    function message_fb_sync_attachments()
    {

        exit; //Logic needs updating

        //Cron Settings: * * * * *

        /*
         * This cron job looks for all requests to sync Message attachments
         * with Facebook, gets them done and marks the engagement as done
         *
         */

        $success_count = 0; //Track success
        $max_per_batch = 5; //Max number of syncs per cron run
        $tr_metadata = array();
        $x_types = echo_status('x_type', null);

        $pending_urls = $this->Db_model->x_fetch(array(
            'x_type >=' => 2,
            'x_type <=' => 5, //These are the file URLs that need syncing
            'x_fb_att_id' => 0, //Pending Facebook Sync
        ), array(), array(), $max_per_batch);


        if (count($pending_urls) > 0) {
            foreach ($pending_urls as $x) {

                $payload = array(
                    'message' => array(
                        'attachment' => array(
                            'type' => $x_types[$x['x_type']]['s_fb_key'],
                            'payload' => array(
                                'is_reusable' => true,
                                'url' => $x['x_url'],
                            ),
                        ),
                    )
                );

                //Attempt to save this:
                $result = $this->Comm_model->fb_graph('POST', '/me/message_attachments', $payload);
                $db_result = false;

                if ($result['status'] && isset($result['tr_metadata']['result']['attachment_id'])) {
                    //Save attachment to DB:
                    $db_result = $this->Db_model->x_update($x['x_id'], array(
                        'x_fb_att_id' => $result['tr_metadata']['result']['attachment_id'],
                    ));
                }

                //Did it go well?
                if ($db_result > 0) {
                    $success_count++;
                } else {

                    //Log error:
                    $this->Db_model->tr_create(array(
                        'tr_content' => 'message_fb_sync_attachments() Failed to sync attachment using Facebook API',
                        'tr_metadata' => array(
                            'payload' => $payload,
                            'result' => $result,
                        ),
                        'tr_en_type_id' => 4246, //Platform Error
                    ));

                    //Disable future attempts:
                    $this->Db_model->x_update($x['x_id'], array(
                        'x_fb_att_id' => -1, //No more checks on this guy
                    ));
                }


                //Save stats either way:
                array_push($tr_metadata, array(
                    'payload' => $payload,
                    'fb_result' => $result,
                ));

            }
        }

        //Echo message for cron job:
        echo_json(array(
            'status' => ($success_count == count($pending_urls) && $success_count > 0 ? 1 : 0),
            'message' => $success_count . '/' . count($pending_urls) . ' Message' . echo__s(count($pending_urls)) . ' successfully synced their attachment with Facebook',
            'tr_metadata' => $tr_metadata,
        ));

    }


    function fix_people_missing_parent()
    {
        //TODO Run on more time later, should return nothing... Then delete this...
        $fetch_us = $this->Db_model->en_fetch(array(
            'u_fb_psid >' => 0,
        ));
        foreach ($fetch_us as $u) {
            if (!filter_array($u['en__parents'], 'en_id', 1278)) {
                //Add parent:
                echo '<a href="/entities/' . $u['u_id'] . '">' . $u['u_full_name'] . '</a><br />';
                $ur1 = $this->Db_model->ur_create(array(
                    'ur_child_u_id' => $u['u_id'],
                    'ur_parent_u_id' => 1278,
                ));
            }
        }


    }

    function student_reminder_complete_task()
    {

        exit; //Needs optimization

        //Will ask the student why they are stuck and try to get them to engage with the material
        //TODO implement social features to maybe connect to other students at the same level
        //The primary function that would pro-actively communicate the subscription to the user
        //If $w_id is provided it would step forward a specific subscription
        //If both $w_id and $u_id are present, it would auto register the user in an idle subscription if they are not part of it yet, and if they are, it would step them forward.

        $bot_settings = array(
            'max_per_run' => 10, //How many subscriptions to server per run (Might include duplicate w_child_u_id's that will be excluded)
            'reminder_frequency_min' => 1440, //Every 24 hours
        );

        //Run even minute by the cron job and determines which users to talk to...
        //Fetch all active subscriptions:
        $user_ids_served = array(); //We use this to ensure we're only service one subscription per user
        $active_ws = $this->Db_model->w_fetch(array(
            'w_status' => 1,
            'u_status >=' => 0,
            'in_status >=' => 2,
            'w_last_heard >=' => date("Y-m-d H:i:s", (time() + ($bot_settings['reminder_frequency_min'] * 60))),
        ), array('in', 'en'), array(
            'w_last_heard' => 'ASC', //Fetch users who have not been served the longest, so we can pay attention to them...
        ), $bot_settings['max_per_run']);

        foreach ($active_ws as $w) {

            if (in_array(intval($w['u_id']), $user_ids_served)) {
                //Skip this as we do not want to handle two subscriptions from the same user:
                continue;
            }

            //Add this user to the queue:
            array_push($user_ids_served, intval($w['u_id']));

            //See where this user is in their subscription:
            $ks_next = $this->Db_model->k_next_fetch($w['w_id']);

            if (!$ks_next) {
                //Should not happen, bug already reported:
                return false;
            }

            //Update the serving timestamp:
            $this->Db_model->w_update($w['w_id'], array(
                'w_last_heard' => date("Y-m-d H:i:s"),
            ));


            //Give them next step again:
            $this->Comm_model->k_next_fetch($w['w_id']);

            //$ks_next[0]['c_outcome']
        }


        exit;

        //Cron Settings: 45 * * * *
        //Send reminders to students to complete their intent:

        $ws = $this->Db_model->w_fetch(array());

        //Define the logic of these reminders
        $reminder_index = array(
            array(
                'time_elapsed' => 0.90,
                'progress_below' => 0.99,
                'reminder_c_id' => 3139,
            ),
            array(
                'time_elapsed' => 0.75,
                'progress_below' => 0.50,
                'reminder_c_id' => 3138,
            ),
            array(
                'time_elapsed' => 0.50,
                'progress_below' => 0.25,
                'reminder_c_id' => 3137,
            ),
            array(
                'time_elapsed' => 0.20,
                'progress_below' => 0.10,
                'reminder_c_id' => 3136,
            ),
            array(
                'time_elapsed' => 0.05,
                'progress_below' => 0.01,
                'reminder_c_id' => 3358,
            ),
        );

        $stats = array();
        foreach ($ws as $subscription) {

            //See what % of the class time has elapsed?
            //TODO calculate $elapsed_class_percentage

            foreach ($reminder_index as $logic) {
                if ($elapsed_class_percentage >= $logic['time_elapsed']) {

                    if ($subscription['w__progress'] < $logic['progress_below']) {

                        //See if we have reminded them already about this:
                        $reminders_sent = $this->Db_model->tr_fetch(array(
                            'tr_en_type_id IN (4280,4276)' => null, //Email or Message sent
                            'tr_en_child_id' => $subscription['u_id'],
                            'tr_in_child_id' => $logic['reminder_c_id'],
                        ));

                        if (count($reminders_sent) == 0) {

                            //Nope, send this message out:
                            $this->Comm_model->compose_messages(array(
                                'tr_en_creator_id' => 0, //System
                                'tr_en_child_id' => $subscription['u_id'],
                                'tr_in_child_id' => $logic['reminder_c_id'],
                            ));

                            //Show in stats:
                            array_push($stats, $subscription['u_full_name'] . ' done ' . round($subscription['w__progress'] * 100) . '% (less than target ' . round($logic['progress_below'] * 100) . '%) where class is ' . round($elapsed_class_percentage * 100) . '% complete and got reminded via c_id ' . $logic['reminder_c_id']);
                        }
                    }

                    //Do not go further down the reminder types:
                    break;
                }
            }
        }

        echo_json($stats);
    }

}