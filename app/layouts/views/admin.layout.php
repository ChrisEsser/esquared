<?php

/** @var $this \Template */

/** @var string $body */
$body = $this->getVar('body');
/** @var string $action */
$action = $this->getVar('action');

?>

<!DOCTYPE html>
<html lang="en">

<head>
    <title>E2 Holdings Property Manager</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css"/>
    <link rel="stylesheet" href="https://unpkg.com/filepond/dist/filepond.css"/>
    <link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css"/>
    <link rel="stylesheet" href="/css/betterButtons.css?ver=105"/>
    <link rel="stylesheet" href="/css/style.css?ver=105"/>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/filepond-plugin-image-resize/dist/filepond-plugin-image-resize.js"></script>
    <script src="https://unpkg.com/filepond/dist/filepond.min.js"></script>
    <script src="https://unpkg.com/jquery-filepond/filepond.jquery.js"></script>
    <script src="/js/confirm.js?ver=105"></script>
    <script src="/js/filepondHelper.js"></script>
    <script src="/js/tableData.js?ver=105"></script>
    <?= HTML::displayHead() ?>
</head>

<body>
<div class="container-fluid">
    <div class="row flex-nowrap">

        <div class="col-auto px-0">

                <div id="sidebar" class="border-end vh-100 shadow-sm">

                    <div id="sidebar-nav" class="list-group border-0">

                        <div class="p-3"><h4 class="mb-0" style="color: #0d6efd; white-space: nowrap">E<sup>2</sup> Admin</h4></div>

    <!--                    <form class="d-flex p-2" style="white-space: nowrap">-->
    <!--                        <input class="form-control me-2 rounded-0" type="search" placeholder="Search" aria-label="Search">-->
    <!--                    </form>-->

                        <div class="menu_heading">Menu</div>

                        <ul class="list_container">
                            <li class="<?=($action == 'dashboard') ? 'active' : ''?>">
                                <a href="/dashboard" tabindex="0">
                                    <div><i class="fa fa-home"></i></div>
                                    <span>Dashboard</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'properties') ? 'active' : ''?>">
                                <a href="/properties" tabindex="0">
                                    <div><i class="fa fa-building"></i></div>
                                    <span>Properties</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'units') ? 'active' : ''?>">
                                <a href="/units" tabindex="0">
                                    <div><i class="fa fa-building-o"></i></div>
                                    <span>Units</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'payments') ? 'active' : ''?>">
                                <a href="/payments" tabindex="0">
                                    <div><i class="fa fa-dollar"></i></div>
                                    <span>Payments</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'documents') ? 'active' : ''?>">
                                <a href="/documents" tabindex="0">
                                    <div><i class="fa fa-file"></i></div>
                                    <span>Documents</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'notes') ? 'active' : ''?>">
                                <a href="/notes" tabindex="0">
                                    <div><i class="fa fa-sticky-note"></i></div>
                                    <span>Notes</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'users') ? 'active' : ''?>">
                                <a href="/users" tabindex="0">
                                    <div><i class="fa fa-users"></i></div>
                                    <span>Users</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'scraper') ? 'active' : ''?>">
                                <a href="/scraper" tabindex="0">
                                    <div><i class="fa fa-crosshairs"></i></div>
                                    <span>Scraper Urls</span>
                                </a>
                            </li>
                            <li class="<?=($action == 'admin') ? 'active' : ''?>">
                                <a href="/admin" tabindex="0">
                                    <div><i class="fa fa-cog"></i></div>
                                    <span>Settings</span>
                                </a>
                            </li>
                        </ul>

                    </div>

                    <a href="#" id="sidebar_trigger"><i class="fa fa-bars"></i></a>

                </div>



<!--            </div>-->

        </div>

        <div class="col" id="body_container">

            <div class="top_bar py-3" style="display: flex; align-items: center; justify-content: end">

                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-dark px-3 text-decoration-none dropdown-toggle" id="dropdownUser1" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa fa-user" style="font-size: 1.4em"></i>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-dark text-small shadow" aria-labelledby="dropdownUser1">
                        <li><a class="dropdown-item" href="/account">Account</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="/logout">Sign out</a></li>
                    </ul>
                </div>

            </div>

            <div class="container-fluid">
                <?= Html::displayAlerts() ?>
                <?= $body ?>
            </div>

        </div>

    </div>
</div>

<div class="modal fade" id="editModal" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="editModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-primary" id="button_save">Save</button>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="viewModal" tabindex="-1" aria-labelledby="viewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="viewModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
        </div>
    </div>
</div>

<div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="confirmModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body"></div>
            <div class="modal-footer">
                <form method="POST" action="">
                    <button type="submit" class="btn btn-primary" id="button_save">Yes</button>
                    <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

    function detectWidthAndHandleSidebar() {
        var windowWidth = window.innerWidth;
        if (windowWidth < 991) {
            $('#sidebar').css({left: '-250px'});
            $('#body_container').css({marginLeft: '0'});
            $('.mobile_header_label').show();
            // $('.tableData_general_container thead tr:last-child').hide();
        } else {
            $('#sidebar').css({left: '0'});
            $('#body_container').css({marginLeft: '250px'});
            $('.tableData_general_container thead tr:last-child').show();
            $('.mobile_header_label').hide();
        }
    }

    detectWidthAndHandleSidebar();

    $(document).ready(function() {

        $(window).resize(() => {
            detectWidthAndHandleSidebar();
        });

        $('#sidebar_trigger').click(function() {

            var lefValue = $('#sidebar').css('left');
            var windowWidth = window.innerWidth;

            if (lefValue == '-250px') {
                $('#sidebar').animate({left: '0'}, 300);
                if (windowWidth < 991) {
                    $('#body_container').css({marginLeft: '0'});
                } else {
                    $('#body_container').animate({marginLeft: '250px'});
                }
            } else {
                $('#sidebar').animate({left: '-250px'}, 300);
                $('#body_container').animate({marginLeft: '0'});
            }

        });

    });

</script>

</body>

</html>