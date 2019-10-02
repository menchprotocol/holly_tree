
<?php $en_all_6206 = $this->config->item('en_all_6206'); //Entity Table ?>
<?php $en_all_4341 = $this->config->item('en_all_4341'); //Link Table ?>
<?php $en_all_7368 = $this->config->item('en_all_7368'); //Trainer App ?>

<script>
    //Set global variables:
    var en_focus_filter = -1; //No filter, show all
    var en_focus_id = <?= $entity['en_id'] ?>;
    var en_all_4592 = <?= json_encode($this->config->item('en_all_4592')) ?>;
</script>
<style>
    .en_child_icon_<?= $entity['en_id'] ?>{ display:none; }
</style>
<script src="/js/custom/en_train.js?v=v<?= $this->config->item('app_version') ?>"
        type="text/javascript"></script>

<div class="row">

    <div class="<?= $this->config->item('css_column_1') ?>">

        <?php

        //Parents
        echo '<h5 class="badge badge-h"><a href="javascript:void(0);" onclick="$(\'.parents-div\').toggleClass(\'hidden\')" style="color: #FFF;"><i class="far fa-plus-circle parents-div"></i><i class="far fa-minus-circle parents-div hidden"></i> <span class="li-parent-count">' . count($entity['en__parents']) . '</span> Parent' . echo__s(count($entity['en__parents'])).'</a></h5>';

        echo '<div class="parents-div hidden">';
        echo '<div id="list-parent" class="list-group  grey-list">';
        foreach ($entity['en__parents'] as $en) {
            echo echo_en($en, 2, true);
        }
        //Input to add new parents:
        echo '<div id="new-parent" class="list-group-item list_input grey-input ' . advance_mode() . '">
                    <div class="form-group is-empty"><input type="text" class="form-control new-input algolia_search" data-lpignore="true" placeholder="Add Entity/URL"></div>
                    <div class="algolia_search_pad hidden"><span>Search existing entities, create a new entity or paste a URL...</span></div>
            </div>';

        echo '</div>';
        echo '</div>';





        //Focused Entity:
        echo '<h5 class="badge badge-h">Entity @'.$entity['en_id'].'</h5>';

        //Hidden link to Metadata:
        if($is_admin){
            echo '<a class="secret" href="/entities/en_review_metadata/' . $entity['en_id'] . '" style="margin-left: 5px;" target="_blank" data-toggle="tooltip" title="Review Entity Metadata" data-placement="bottom"><i class="fas fa-function"></i></a>';

            echo '<a class="secret" href="/links/cron__sync_algolia/en/' . $entity['en_id'] . '" style="margin-left: 5px;" target="_blank" data-toggle="tooltip" title="Update Algolia Search Index" data-placement="bottom"><i class="fas fa-search"></i></a>';
        }

        echo '<div id="entity-box" class="list-group">';
        echo echo_en($entity, 1);
        echo '</div>';



        //Children:
        echo '<table width="100%" style="margin-top:10px;"><tr>';
        echo '<td style="width:170px;">';


            echo '<h5 class="badge badge-h inline-block"><span class="li-children-count inline-block">' . $entity['en__child_count'] . '</span> Children</h5>';

            echo '<span class="' . advance_mode() . '"><a href="javascript:void(0);" onclick="$(\'.mass_modify\').toggleClass(\'hidden\');mass_action_ui();" style="text-decoration: none; margin-left: 5px;"  data-toggle="tooltip" data-placement="right" title="Mass Update Children"><i class="fal fa-list-alt" style="font-size: 1.2em; color: #2b2b2b;"></i></a></span>';

            echo '</td>';


        echo '<td style="text-align: right;">';
        echo '<div class="btn-group btn-group-sm" style="margin-top:-5px;" role="group">';

        //Fetch current count for each status from DB:
        $child_en_filters = $this->Links_model->ln_fetch(array(
            'ln_parent_entity_id' => $entity['en_id'],
            'ln_type_entity_id IN (' . join(',', $this->config->item('en_ids_4592')) . ')' => null, //Entity-to-Entity Links
            'ln_status_entity_id IN (' . join(',', $this->config->item('en_ids_7360')) . ')' => null, //Link Statuses Active
            'en_status_entity_id IN (' . join(',', $this->config->item('en_ids_7358')) . ')' => null, //Entity Statuses Active
        ), array('en_child'), 0, 0, array('en_status_entity_id' => 'ASC'), 'COUNT(en_id) as totals, en_status_entity_id', 'en_status_entity_id');


        //Only show filtering UI if we find child entities with different statuses (Otherwise no need to filter):
        if (count($child_en_filters) > 0 && $child_en_filters[0]['totals'] < $entity['en__child_count']) {

            //Load status definitions:
            $en_all_6177 = $this->config->item('en_all_6177'); //Entity Statuses

            //Show fixed All button:
            echo '<a href="#" onclick="en_filter_status(-1)" class="btn btn-default btn-secondary u-status-filter u-status--1" data-toggle="tooltip" data-placement="top" title="View all entities"><i class="fas fa-at"></i><span class="hide-small"> All</span> [<span class="li-children-count">' . $entity['en__child_count'] . '</span>]</a>';

            //Show each specific filter based on DB counts:
            foreach ($child_en_filters as $c_c) {
                $st = $en_all_6177[$c_c['en_status_entity_id']];
                echo '<a href="#status-' . $c_c['en_status_entity_id'] . '" onclick="en_filter_status(' . $c_c['en_status_entity_id'] . ')" class="btn btn-default u-status-filter u-status-' . $c_c['en_status_entity_id'] . '" data-toggle="tooltip" data-placement="top" title="' . $st['m_desc'] . '">' . $st['m_icon'] . '<span class="hide-small"> ' . $st['m_name'] . '</span> [<span class="count-u-status-' . $c_c['en_status_entity_id'] . '">' . $c_c['totals'] . '</span>]</a>';
            }

        }

        echo '</div>';
        echo '</td>';
        echo '</tr></table>';



        echo '<form class="mass_modify hidden" method="POST" action="" style="width: 100% !important;"><div class="inline-box">';


            $dropdown_options = '';
            $input_options = '';
            foreach ($this->config->item('en_all_4997') as $action_en_id => $mass_action_en) {

                $dropdown_options .= '<option value="' . $action_en_id . '">' .$mass_action_en['m_name'] . '</option>';


                //Start with the input wrapper:
                $input_options .= '<span id="mass_id_'.$action_en_id.'" class="inline-block hidden mass_action_item">';

                $input_options .= '<i class="fal fa-info-circle" data-toggle="tooltip" data-placement="right" title="'.$mass_action_en['m_desc'].'"></i> ';

                if(in_array($action_en_id, array(5000, 5001, 10625))){

                    //String Find and Replace:

                    //Find:
                    $input_options .= '<input type="text" name="mass_value1_'.$action_en_id.'" placeholder="Search" style="width: 145px;" class="form-control border">';

                    //Replace:
                    $input_options .= '<input type="text" name="mass_value2_'.$action_en_id.'" placeholder="Replace" stycacle="width: 145px;" class="form-control border">';


                } elseif(in_array($action_en_id, array(5981, 5982))){

                    //Entity search box:

                    //String command:
                    $input_options .= '<input type="text" name="mass_value1_'.$action_en_id.'" style="width:300px;" placeholder="Search entities..." class="form-control algolia_search en_quick_search border">';

                    //We don't need the second value field here:
                    $input_options .= '<input type="hidden" name="mass_value2_'.$action_en_id.'" value="" />';


                } elseif($action_en_id == 5003){

                    //Entity Status update:

                    //Find:
                    $input_options .= '<select name="mass_value1_'.$action_en_id.'" class="form-control border">';
                    $input_options .= '<option value="">Set Condition...</option>';
                    $input_options .= '<option value="*">Update All Statuses</option>';
                    foreach($this->config->item('en_all_6177') /* Entity Statuses */ as $en_id => $m){
                        $input_options .= '<option value="'.$en_id.'">Update All '.$m['m_name'].'</option>';
                    }
                    $input_options .= '</select>';

                    //Replace:
                    $input_options .= '<select name="mass_value2_'.$action_en_id.'" class="form-control border">';
                    $input_options .= '<option value="">Set New Status...</option>';
                    foreach($this->config->item('en_all_6177') /* Entity Statuses */ as $en_id => $m){
                        $input_options .= '<option value="'.$en_id.'">Set to '.$m['m_name'].'</option>';
                    }
                    $input_options .= '</select>';


                } elseif($action_en_id == 5865){

                    //Link Status update:

                    //Find:
                    $input_options .= '<select name="mass_value1_'.$action_en_id.'" class="form-control border">';
                    $input_options .= '<option value="">Set Condition...</option>';
                    $input_options .= '<option value="*">Update All Statuses</option>';
                    foreach($this->config->item('en_all_6186') /* Link Statuses */ as $en_id => $m){
                        $input_options .= '<option value="'.$en_id.'">Update All '.$m['m_name'].'</option>';
                    }
                    $input_options .= '</select>';

                    //Replace:
                    $input_options .= '<select name="mass_value2_'.$action_en_id.'" class="form-control border">';
                    $input_options .= '<option value="">Set New Status...</option>';
                    foreach($this->config->item('en_all_6186') /* Link Statuses */ as $en_id => $m){
                        $input_options .= '<option value="'.$en_id.'">Set to '.$m['m_name'].'</option>';
                    }
                    $input_options .= '</select>';


                } else {

                    //String command:
                    $input_options .= '<input type="text" name="mass_value1_'.$action_en_id.'" style="width:300px;" placeholder="String..." class="form-control border">';

                    //We don't need the second value field here:
                    $input_options .= '<input type="hidden" name="mass_value2_'.$action_en_id.'" value="" />';

                }

                $input_options .= '</span>';

            }

            echo '<select class="form-control border inline-block" name="mass_action_en_id" id="set_mass_action">';
            echo $dropdown_options;
            echo '</select>';

            echo $input_options;

            echo '<input type="submit" value="Apply" class="btn btn-secondary inline-block">';

        echo '</div></form>';




        //Private hack for now:
        //TODO Build UI for this via Github Issue #2354
        $set_sort = ( isset($_GET['set_sort']) ? $_GET['set_sort'] : 'none' );
        echo '<input type="hidden" id="set_sort" value="'.$set_sort.'" />'; //For JS to pass to the next page loader...


        echo '<div id="list-children" class="list-group grey-list">';


        $en__children = $this->Links_model->ln_fetch(array(
            'ln_parent_entity_id' => $entity['en_id'],
            'ln_type_entity_id IN (' . join(',', $this->config->item('en_ids_4592')) . ')' => null, //Entity-to-Entity Links
            'ln_status_entity_id IN (' . join(',', $this->config->item('en_ids_7360')) . ')' => null, //Link Statuses Active
            'en_status_entity_id IN (' . join(',', $this->config->item('en_ids_7358')) . ')' => null, //Entity Statuses Active
        ), array('en_child'), $this->config->item('items_per_page'), 0, sort_entities($set_sort));

        foreach ($en__children as $en) {
            echo echo_en($en, 2);
        }
        if ($entity['en__child_count'] > count($en__children)) {
            echo_en_load_more(1, $this->config->item('items_per_page'), $entity['en__child_count']);
        }


        //Input to add new child:
        echo '<div id="new-children" class="list-group-item list_input grey-input '. advance_mode() .'">
            <div class="form-group is-empty"><input type="text" class="form-control new-input algolia_search" data-lpignore="true" placeholder="Add Entity/URL"></div>
            <div class="algolia_search_pad hidden"><span>Search existing entities, create a new entity or paste a URL...</span></div>
    </div>';
        echo '</div>';

        ?>
    </div>

    <div class="<?= $this->config->item('css_column_2') ?>">
        <?php $this->load->view('view_trainer_app/en_modify'); ?>
    </div>

</div>

<div style="height: 50px;">&nbsp;</div>