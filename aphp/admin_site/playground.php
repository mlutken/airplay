<?php
require_once ( __DIR__ . '/../../aphp/aphp_fix_include_path.php' );
require_once ('airplay_globals.php');
require_once ('admin_site/classes/session_start.php');
require_once ('admin_site/classes/PagesCommon.php');
require_once ('admin_site/classes/SimpleTableUI.php');

$name = 'RecordStore';
$tblBaseName    = 'record_store';
$pc = new PagesCommon();
$ui = new SimpleTableUI($name,$tblBaseName);

echo $pc->pageStart("Admin: {$name}");

//array( 'record_store_name', 'record_store_url', 'country_id', 'use_affiliate', 'affiliate_link', 'affiliate_encode_times', 'record_store_reliability' );

// /*
// if ($_SESSION['logged_in'] == 1 ) {
//     echo "<h1>Logged in</h1>";
// }
// else {
//     echo "<h1>Logged out</h1>";
// }*/

?>
<div id=incrementalSearchID > 
Search: <input id=incrementalSearchInputID type=text size=14 onkeyup="incrementalSearch(this,'RecordStore', false);" ></input>
&nbsp;<input id=incrementalSearchClearID  type=button value='Clear search' onclick="incrementalSearch(0,'RecordStore', true);" ></input>
</div>
<br />

<?php
echo $ui->containerDIV();

?>
    <script type="text/javascript">

        $(document).ready(function () {

            //Prepare jTable
            $('#RecordStoreTableContainer').jtable({
                title: 'RecordStore',
                paging: true,
                pageSize: 10,
//                 sorting: true,
//                 defaultSorting: 'record_store_name ASC',
                actions: {
                    listAction: 'ajax_handlers/RecordStore_handler.php?action=list',
                    createAction: 'ajax_handlers/RecordStore_handler.php?action=create',
                    updateAction: 'ajax_handlers/RecordStore_handler.php?action=update',
                    deleteAction: 'ajax_handlers/RecordStore_handler.php?action=delete'
                },
                fields: {
                    record_store_id: {
                        title: 'record_store_id',
                        key: true,
                        create: false,
                        edit: false,
//                         list: false
                    },
                    record_store_name: {
                        title: 'record_store_name',
                        width: '10%'
                    },
                    record_store_url: {
                        title: 'record_store_url',
                        width: '10%'
                    },
                    country_id: {
                        title: 'country_id',
                        width: '10%'
                    },
                    use_affiliate: {
                        title: 'use_affiliate',
                        width: '10%',
                        type: 'bool',
                    },
                    affiliate_link: {
                        title: 'affiliate_link',
                        width: '10%'
                    },
                    affiliate_encode_times: {
                        title: 'affiliate_encode_times',
                        width: '10%'
                    },
                    record_store_reliability: {
                        title: 'record_store_reliability',
                        width: '10%'
                    },
                    TestColumn: {
                        title: 'Test',
                        display: function (data) {
                            return '<b>test</b>';
                        }
                    }                 
                               
                }
            });

            //Load person list from server
            $('#RecordStoreTableContainer').jtable('load');

        });

    </script>
<?php
echo $pc->pageEnd();

//                     RecordDate: {
//                         title: 'Record date',
//                         width: '30%',
//                         type: 'date',
//                         create: false,
//                         edit: false
//                     },


?>