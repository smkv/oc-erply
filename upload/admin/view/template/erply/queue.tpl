<?php echo $header; ?><?php echo $column_left; ?>
<div id="content">

    <div class="page-header">
        <div class="container-fluid">

            <h1><?php echo $heading_title; ?></h1>
            <ul class="breadcrumb">
                <?php foreach ($breadcrumbs as $breadcrumb) { ?>
                <li><a href="<?php echo $breadcrumb['href']; ?>"><?php echo $breadcrumb['text']; ?></a></li>
                <?php } ?>
            </ul>
        </div>
    </div>


    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="pull-right">
                <a class="btn btn-danger btn-sm" href="<?php echo $check_new;?>">Check new products</a>
            </div>
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        </div>
        <div class="panel-body">

            <div class="well">

                <div class="input-group">
                    <input id="erply_queue_filter" name="filter" class="form-control" value="<?=$filter?>">
                    <span class="input-group-btn">
                        <button type="button" class="btn btn-primary" onclick="window.location.href='<?=$filter_action?>&filter='+ $('#erply_queue_filter').val(); ">Filter</button>
                    </span>
                </div>

            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                    <tr>
                        <th>ID</th>
                        <th>EAN</th>
                        <th>Name</th>
                        <th>Group</th>
                        <th>Seria</th>
                        <th width="150">Action</th>
                    </tr>
                    </thead>
                    <tbody>
                    <?php if ($queue) { ?>
                    <?php foreach ($queue as $product) { ?>
                    <tr>
                        <td><?=$product['erply_product_id']?></td>
                        <td><?=$product['erply_product_ean']?></td>
                        <td><?=$product['erply_product_name']?></td>
                        <td><?=$product['erply_product_group']?></td>
                        <td><?=$product['erply_product_seria']?></td>
                        <td>


                            <div class="btn-group">
                                <button class="btn btn-success"
                                        onclick="ErplyQueue.addFormAndSubmit('<?=$product['add_action']?>','erply_product_id','<?=$product['erply_product_id']?>')">
                                    Add
                                </button><button class="btn btn-default"
                                        onclick="ErplyQueue.addFormAndSubmit('<?=$product['skip_action']?>','erply_product_id','<?=$product['erply_product_id']?>')">
                                    Skip
                                </button>

                            </div>
                        </td>
                    </tr>
                    <?php } ?>
                    <?php } else { ?>
                    <tr>
                        <td class="text-center" colspan="6"><?php echo $text_no_results; ?></td>
                    </tr>
                    <?php } ?>
                    </tbody>
                </table>
            </div>
            <div class="row">
                <div class="col-sm-6 text-left"><?php echo $pagination; ?></div>
                <div class="col-sm-6 text-right"><?php echo $results; ?></div>
            </div>
        </div>
    </div>


    <script>
        var ErplyQueue =  {

            addFormAndSubmit: function (action, param, value) {
                var form =
                        $('<form />')
                                .attr('method', 'post')
                                .attr('action', action)
                                .append($('<input/>').attr('name', param).val(value));
                $(document.body).append(form);
                form.submit();

            }

        }
    </script>


</div>
<?php echo $footer; ?>