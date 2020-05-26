<?php

$stats = array(
    'ideas' => 0,
    'source_missing' => 0,
    'note_deleted' => 0,
    'is_deleted' => 0,
    'creator_missing' => 0,
    'creator_extra' => 0,
    'creator_fixed' => 0,
    'source_duplicate' => 0,
);

//FInd and delete duplicate sources:
foreach($this->IDEA_model->fetch() as $in) {

    $stats['ideas']++;

    $is_deleted = !in_array($in['idea__status'], $this->config->item('sources_id_7356'));

    //Scan sources:
    $idea_sources = $this->READ_model->fetch(array(
        'read__status IN (' . join(',', $this->config->item('sources_id_7359')) . ')' => null, //PUBLIC
        'read__type IN (' . join(',', $this->config->item('sources_id_12273')) . ')' => null, //IDEA COIN
        'read__right' => $in['idea__id'],
        'read__up >' => 0, //MESSAGES MUST HAVE A SOURCE REFERENCE TO ISSUE IDEA COINS
        ' EXISTS (SELECT 1 FROM mench_interactions WHERE source__id=read__down AND read__up=4430 AND read__type IN (' . join(',', $this->config->item('sources_id_4592')) . ') AND read__status IN ('.join(',', $this->config->item('sources_id_7359')) /* PUBLIC */.')) ' => null,
    ));
    $idea_sources = $this->READ_model->fetch(array(
        'read__type' => 4250, //New Idea Created
        'read__right' => $in['idea__id'],
    ), array(), 0, 0, array('read__id' => 'ASC')); //Order in case we have extra & need to remove
    $idea_notes = $this->READ_model->fetch(array( //Idea Links
        'read__status IN (' . join(',', $this->config->item('sources_id_7360')) . ')' => null, //ACTIVE
        'read__type IN (' . join(',', $this->config->item('sources_id_4485')) . ')' => null, //IDEA NOTES
        'read__right' => $in['idea__id'],
    ), array(), 0);

    if(!count($idea_sources)) {
        $stats['creator_missing']++;
        $this->READ_model->create(array(
            'read__source' => $session_source['source__id'],
            'read__right' => $in['idea__id'],
            'read__message' => $in['idea__title'],
            'read__type' => 4250, //New Idea Created
        ));
    } elseif(count($idea_sources) >= 2) {
        //Remove extra:
        foreach($idea_sources as $count => $idea_source_tr){
            if($count == 0){
                continue; //Keep first one
            } else {
                $stats['creator_extra']++;
                $this->db->query("DELETE FROM mench_interactions WHERE read__id=".$idea_source_tr['read__id']);
            }
        }
    }


    if(!$is_deleted && !count($idea_sources)){

        //Missing SOURCE

        $stats['source_missing']++;
        $creator_id = ( count($idea_sources) ? $idea_sources[0]['read__source'] : $session_source['source__id'] );
        $this->READ_model->create(array(
            'read__type' => 4983, //IDEA COIN
            'read__source' => $creator_id,
            'read__up' => $creator_id,
            'read__message' => '@'.$creator_id,
            'read__right' => $in['idea__id'],
        ));

    } elseif($is_deleted && count($idea_notes)){

        //Extra SOURCES
        foreach($idea_notes as $idea_note){
            //Delete this link:
            $stats['note_deleted'] += $this->READ_model->update($idea_note['read__id'], array(
                'read__status' => 6173, //Link Deleted
            ), $session_source['source__id'], 10686 /* Idea Link Unpublished */);
        }

    } elseif(count($idea_sources) >= 2){

        //See if duplicates:
        $found_duplicate = false;
        $sources = array();
        foreach($idea_sources as $idea_source){
            if(!in_array($idea_source['read__up'], $sources)){
                array_push($sources, $idea_source['read__up']);
            } else {
                $found_duplicate = true;
                break;
            }
        }

        if($found_duplicate){
            $stats['source_duplicate']++;
        }

    }
}

echo nl2br(print_r($stats, true));