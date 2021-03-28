
</div><!-- Container -->

<?php
$member_e = superpower_unlocked();
if($member_e && ( !isset($basic_header_footer) || !$basic_header_footer )){

    $e___11035 = $this->config->item('e___11035'); //NAVIGATION
    $e___14393 = $this->config->item('e___14393'); //SUGGEST
    $e___13571 = $this->config->item('e___13571'); //SOURCE EDITOR
    $e___14937 = $this->config->item('e___14937'); //SOURCE ICON

    ?>

    <!-- ACCOUNT SETTINGS Modal -->
    <div class="modal fade indifferent" id="modal6225" tabindex="-1" role="dialog" aria-labelledby="modal6225Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal6225Label"><?= $e___11035[6225]['m__cover'].' '.$e___11035[6225]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <?= view_e_settings(6225, false) ?>
                </div>
            </div>
        </div>
    </div>









    <!-- SUGGEST Modal -->
    <div class="modal fade indifferent" id="modal14393" tabindex="-1" role="dialog" aria-labelledby="modal14393Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal14393Label"><?= $e___11035[14393]['m__cover'].' '.$e___11035[14393]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <?php

                    //Current URL:
                    echo '<div class="headline"><span class="icon-block">'.$e___14393[14927]['m__cover'].'</span>'.$e___14393[14927]['m__title'].'</div>';
                    echo '<div class="current_url padded hideIfEmpty inline-block"></div>';



                    //Type
                    echo '<div class="headline top-margin"><span class="icon-block">'.$e___14393[14394]['m__cover'].'</span>'.$e___14393[14394]['m__title'].'</div>';
                    $counter_options = 0;
                    foreach($this->config->item('e___14394') /* SUGGESTION TYPE */ as $x__type => $m){
                        $counter_options++;
                        echo '<div class="form-check">
                    <input class="form-check-input" type="radio" name="sugg_type" id="formRadio'.$x__type.'" value="'.$x__type.'" '.( $counter_options==1 ? ' checked ' : '' ).'>
                    <label class="form-check-label" for="formRadio'.$x__type.'">' . $m['m__title'] . '</label>
                </div>';
                    }

                    //Details
                    echo '<div class="headline top-margin"><span class="icon-block">'.$e___14393[14395]['m__cover'].'</span>'.$e___14393[14395]['m__title'].'</div>';
                    echo '<div class="padded"><textarea class="form-control text-edit border" id="sugg_note" data-lpignore="true" placeholder="More details here..."></textarea></div>';

                    ?>


                </div>
                <div class="modal-footer">
                    <button type="button" onclick="x_suggestion()" class="btn btn-default">SUBMIT</button>
                </div>
            </div>
        </div>
    </div>







    <!-- SHARE Modal -->
    <div class="modal fade" id="modal13024" tabindex="-1" role="dialog" aria-labelledby="modal13024Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal13024Label"><?= $e___11035[13024]['m__cover'].' '.$e___11035[13024]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">

                    <?php
                    //URL
                    $current_link = current_link();
                    echo '<div class="headline"><span class="icon-block">'.$e___14393[14927]['m__cover'].'</span>'.$e___14393[14927]['m__title'].'</div>';
                    echo '<div class="padded"><a href="javascript:void();" onclick="copyTextToClipboard(\''.$current_link.'\');">'.$current_link.'&nbsp;&nbsp;<i class="fa fa-gif-wrap was_copied">COPY</i></a></div>';

                    ?>

                </div>
            </div>
        </div>
    </div>




    <!-- APP Modal -->
    <div class="modal fade indifferent" id="modal6287" tabindex="-1" role="dialog" aria-labelledby="modal6287Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal14393Label"><?= $e___11035[6287]['m__cover'].' '.$e___11035[6287]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Loads dynamically -->
                    <i class="far fa-yin-yang fa-spin"></i> Loading...
                </div>
            </div>
        </div>
    </div>




    <!-- EDIT MESSAGE Modal -->
    <div class="modal fade" id="modal13571" tabindex="-1" role="dialog" aria-labelledby="modal13571Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal13571Label"><?= $e___11035[13571]['m__cover'].' '.$e___11035[13571]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <textarea class="form-control text-edit border" id="x__message" name="x__message" data-lpignore="true" placeholder="<?= $e___13571[4372]['m__message'] ?>"></textarea>
                    <div id="x__message_preview" class="hideIfEmpty" style="width: 377px;"></div>
                    <div class="save_results margin-top-down-half hideIfEmpty"></div>
                    <input type="hidden" class="modal_x__id" value="0" />
                </div>
                <div class="modal-footer">
                    <table style="width: 100%;">
                        <tr>
                            <td width="100%">
                                <div id="x__type_preview" class="hideIfEmpty"></div>
                            </td>
                            <td>
                                <button type="button" onclick="x_message_save()" class="btn btn-default">SAVE</button>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>
    </div>





    <!-- SET COVER Modal -->
    <div class="modal fade" id="modal14937" tabindex="-1" role="dialog" aria-labelledby="modal14937Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal14393Label"><?= $e___11035[14937]['m__cover'].' '.$e___11035[14937]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>

                <div class="modal-body block_search_results">

                    <!-- IMAGE DROP STARTS -->
                    <div class="coverUploader">
                    <form class="box coverUpload" method="post" enctype="multipart/form-data">


                    <!-- COVER PREVIEW -->
                    <a name="preview_cover" style="height: 1px;">&nbsp;</a>
                    <div class="coin_cover demo_cover" style="width: 255px !important; margin:-21px auto 74px !important;">
                        <div class="cover-wrapper"><div class="black-background cover-link" style=""><div class="cover-btn"></div></div></div>
                        <div class="cover-content"><div class="inner-content"><span><textarea placeholder="Title" id="coin__title" class="form-control css__title inline-block texttype__lg center" style="overflow: hidden;overflow-wrap: break-word;height: 42px;"></textarea></span></div></div>
                    </div>

                    <!-- Power Edit (Required Superpower) -->
                    <div class="<?= superpower_active(14003) ?>"><input type="text" id="coin__cover" value="" data-lpignore="true" placeholder="Emoji, Image URL or Icon Code" class="form-control border-dotted" style="margin-top: 5px;"></div>
                    <input type="hidden" id="coin__type" value="0" />
                    <input type="hidden" id="coin__id" value="0" />


                    <!-- CONTROLLER -->
                    <table style="width: 100%; margin-bottom: 34px;">
                        <tr>
                            <td style="width: 100%;">
                                <!-- SEARCH -->
                                <input id="search_cover" type="text" class="form-control text-edit border-dotted no-padding cover_query algolia_search" placeholder="Search or Paste Image URL..." data-lpignore="true" />

                            </td>
                            <td>
                                <!-- DELETE -->
                                <a class="icon-block" href="javascript:void(0);" title="Clear Cover" onclick="update__cover('')"><i class="fas fa-trash-alt"></i></a>
                            </td>
                            <td>
                                <!-- UPLOAD -->
                                <input class="inputfile" type="file" name="file" id="coverUpload" />
                                <label class="icon-block" for="coverUpload"><?= $e___11035[25990]['m__cover'] ?></label>
                            </td>
                            <td>
                                <!-- SAVE -->
                                <button type="button" onclick="coin__save()" class="btn btn-default">SAVE</button>
                            </td>
                        </tr>
                    </table>


                    <!-- IMAGE DROP ENDS -->
                    </div>
                    </form>


                    <div id="upload_results"></div>
                    <div id="img_results_icons" class="icons_small"></div>
                    <div class="doclear">&nbsp;</div>
                    <div id="img_results_emojis" class="icons_small"></div>
                    <div class="doclear">&nbsp;</div>
                    <div id="img_results_local" class="icons_large"></div>
                    <div id="img_results_tenor" class="icons_large"></div>
                    <div id="img_results_unsplash" class="icons_large"></div>
                    <div class="doclear">&nbsp;</div>

                </div>
            </div>
        </div>
    </div>





    <!-- GIF Modal -->
    <div class="modal fade" id="modal14073" tabindex="-1" role="dialog" aria-labelledby="modal14073Label" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title css__title" id="modal14073Label"><?= $e___11035[14073]['m__cover'].' '.$e___11035[14073]['m__title'] ?></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_i__id" value="0" />
                    <input type="hidden" id="modal_x__type" value="0" />
                    <input type="text" class="form-control text-edit border css__title images_query" placeholder="Search GIFs..." onkeyup="images_search($('.images_query').val())" data-lpignore="true" />
                    <div class="row new_images margin-top-down hideIfEmpty"></div>
                </div>
            </div>
        </div>
    </div>


    <?php

}

?>


</body>
</html>