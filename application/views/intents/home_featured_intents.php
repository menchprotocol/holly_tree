<div>
    <h3>Hi, I'm Mench, a human-trained personal assistant on a mission to <?= $this->config->item('primary_in_name') ?>
        .</h3>
    <h3>How can I help you today?</h3>
    <div class="list-group actionplan_list" style="margin-top: 10px;">
        <?php
        foreach ($featured_cs as $featured_c) {
            echo echo_featured_c($featured_c);
        }
        ?>
    </div>
</div>