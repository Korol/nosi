<!-- Latest compiled and minified CSS -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css"
      integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
<!-- Optional theme -->
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css"
      integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
<!-- Latest compiled and minified JavaScript -->
<script src="<?= base_url('assets/js/jquery-1.9.1.js'); ?>"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"
        integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
<!-- Moment.js -->
<script src="<?= base_url('assets/newdesign/js/moment-with-locales.min.js'); ?>"></script>
<!-- Bootstrap-datetimepicker.js -->
<script src="<?= base_url('assets/newdesign/js/bootstrap-datetimepicker.min.js'); ?>"></script>
<!-- Bootstrap-datetimepicker.css -->
<link rel="stylesheet" href="<?= base_url('assets/newdesign/css/bootstrap-datetimepicker.min.css'); ?>">

<style>
    /* New Bootstrap navbar conflicts fixes */

    .navbar-inner {
        height: 42px;
    }
    .navbar .brand {
        padding-top: 5px;
    }
    .navbar .nav .dropdown-toggle .caret {
        margin-top: 0;
    }
    #adminStructure1 .well {
        min-height: 20px;
        padding: 19px;
        margin-bottom: 20px;
        background-color: #f5f5f5;
        border: 1px solid #e3e3e3;
        -webkit-border-radius: 4px;
        -moz-border-radius: 4px;
        border-radius: 4px;
        -webkit-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
        -moz-box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
        box-shadow: inset 0 1px 1px rgba(0,0,0,0.05);
    }
    #adminStructure1 .nav-list {
        padding-right: 15px;
        padding-left: 15px;
        margin-bottom: 0;
    }
    #adminStructure1 .nav-list>li>a {
        padding: 3px 15px;
    }
    /* end */

    /* page styles */

    .no-ml {
        margin-left: 0;
    }
    .action-container {
        padding-left: 0;
    }
    .ok-indicator {
        font-size: 18px;
        margin-left: 20px;
        color: green;
        display: none;
        position: relative;
        top: 5px;
    }
    .f-req {
        color: red;
        margin-right: 5px;
    }
    .glyphicon-calendar {
        cursor: pointer;
    }
    .show-all-btn {
        margin-top: 25px;
    }
    .sic-rel {
        padding-top: 3px;
        padding-bottom: 3px;
    }
    .check-batch {
        width: 50px;
    }
    .percent-batch {
        width: 150px;
    }
    .batch-percent {
        margin-bottom: 0 !important;
    }
    .check-batch {
        text-align: center !important;
    }
    .action-products-count {
        display: inline-block;
        margin-top: 35px;
    }
    #action_flash_block {
        position: absolute;
        width: 100%;
        top: -20px;
    }
</style>