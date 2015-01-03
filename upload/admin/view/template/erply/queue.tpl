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
                <a class="btn btn-danger btn-sm" href="<?php echo $check_new;?>" >Check new products</a>
            </div>
            <h3 class="panel-title"><i class="fa fa-list"></i> <?php echo $text_list; ?></h3>
        </div>
        <div class="panel-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                        <tr>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Group</th>
                            <th>Seria</th>
                            <th>Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php if ($queue) { ?>
                        <?php foreach ($queue as $product) { ?>
                        <tr>
                            <td><?=$product['erply_product_id']?></td>
                            <td><?=$product['erply_product_name']?></td>
                            <td><?=$product['erply_product_group']?></td>
                            <td><?=$product['erply_product_seria']?></td>
                            <td>
                                <form action="<?=$product['add_action']?>" method="post">
                                    <input type="hidden" name="erply_product_id" value="<?=$product['erply_product_id']?>">
                                    <button class="btn btn-success">Add & Edit</button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>
                        <?php } else { ?>
                        <tr>
                            <td class="text-center" colspan="5"><?php echo $text_no_results; ?></td>
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




</div>
<?php echo $footer; ?>