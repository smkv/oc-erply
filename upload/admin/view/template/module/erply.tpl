<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">





    <div class="page-header">
        <div class="container-fluid">
            <div class="pull-right">
                <button type="submit" form="form-erply" data-toggle="tooltip" title="<?php echo $button_save; ?>"
                        class="btn btn-primary"><i class="fa fa-save"></i></button>
                <a href="<?php echo $cancel; ?>" data-toggle="tooltip" title="<?php echo $button_cancel; ?>"
                   class="btn btn-default"><i class="fa fa-reply"></i></a></div>
            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>

    <div class="container-fluid">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $text_edit; ?></h3>
            </div>
            <div class="panel-body">
                <form action="<?php echo $action; ?>" method="post" enctype="multipart/form-data" id="form-account"
                      class="form-horizontal">



                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="erply_url"><?php echo $text_url; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="erply_url" value="<?php echo $erply_url; ?>" placeholder="<?php echo $text_url; ?>" id="erply_url" class="form-control" />
                            <?php if (!empty($error_erply_url)) { ?>
                            <div class="text-danger"><?php echo $error_erply_url; ?></div>
                            <?php } ?>
                        </div>
                    </div>


                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="erply_client"><?php echo $text_client; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="erply_client" value="<?php echo $erply_client; ?>" placeholder="<?php echo $text_client; ?>" id="erply_client" class="form-control" />
                            <?php if (!empty($error_erply_client)) { ?>
                            <div class="text-danger"><?php echo $error_erply_client; ?></div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="erply_username"><?php echo $text_username; ?></label>
                        <div class="col-sm-10">
                            <input type="text" name="erply_username" value="<?php echo $erply_username; ?>" placeholder="<?php echo $text_username; ?>" id="erply_username" class="form-control" />
                            <?php if (!empty($error_erply_username)) { ?>
                            <div class="text-danger"><?php echo $error_erply_username; ?></div>
                            <?php } ?>
                        </div>
                    </div>

                    <div class="form-group required">
                        <label class="col-sm-2 control-label" for="erply_password"><?php echo $text_password; ?></label>
                        <div class="col-sm-10">
                            <input type="password" name="erply_password" value="<?php echo $erply_password; ?>" placeholder="<?php echo $text_password; ?>" id="erply_password" class="form-control" />
                            <?php if (!empty($error_erply_password)) { ?>
                            <div class="text-danger"><?php echo $error_erply_password; ?></div>
                            <?php } ?>
                        </div>
                    </div>


            </div>
        </div>
    </div>
</div>
<?php echo $footer; ?>