<?php

require_once ('utils/string_utils.php');

class SimpleTableUI
{
    // ------------------------
    // --- Constructor init --- 
    // ------------------------
    public function __construct( $mainName, $baseTableName )
    {
        $this->m_mainName = $mainName;
        $this->m_baseTableName = $baseTableName;
    }
    
    public function dbInterfaceSet( $dbInterface  )
    {
        $this->m_dbInterface = $dbInterface;
    }

    public function actionsAvailableSet( $aActionsAvailable  )
    {
        $this->m_aActionsAvailable = $aActionsAvailable;
    }

    public function fieldsToListSet( $aFieldsToList  )
    {
        $this->m_aFieldsToList = $aFieldsToList;
    }
    
    public function fieldsToEditSet( $aFieldsToEdit  )
    {
        $this->m_aFieldsToEdit = $aFieldsToEdit;
    }

    public function fieldsToCreateSet( $aFieldsToCreate  )
    {
        $this->m_aFieldsToCreate = $aFieldsToCreate;
    }
    
    public function fieldWidthsSet( $aFieldWidths  )
    {
        $this->m_aFieldWidths = $aFieldWidths;
    }

    /** Set additional table options. 
     */
    public function tableOptionsSet( $aTableOptions )
    {
        $this->m_aTableOptions = $aTableOptions;
    }
    
    /** Set width of HTML table displayed. By default entire page is used. 
    Set to for example '800px' if you have a small table to display. */
    public function tableWidthSet( $tableWidth )
    {
        $this->m_tableWidth = $tableWidth;
    }
    
    public function extraGETParametersSet($extraGETParameters)
    {
        $this->m_extraGETParameters = $extraGETParameters;
    }
    
    public function pageContents()
    {
        if ( 1 != $_SESSION['logged_in'] ) return '';
        $s  = $this->containerDIV( $this->m_tableWidth );
        $s .= $this->pageScriptSection();
        return $s;
    }

    public function ajaxHandler()
    {
        $nameViewMode       = "{$this->m_mainName}_viewMode";
        $nameSearchString   = "{$this->m_mainName}_searchString";
        $jTableResult       = array();


        try
        {
            $aData = array();
            foreach ( $_POST as $k => $v ) $aData[ trim($k) ] = trim($v);
            if ( $_POST['viewMode'] != ''       ) $_SESSION[$nameViewMode]      = $_POST['viewMode'];
            if ( $_POST['searchString'] != ''   ) $_SESSION[$nameSearchString]  = $_POST['searchString'];
            
            $this->m_listAllStartIndex  = (int)($_GET['jtStartIndex']);
            $this->m_listAllCount       = (int)($_GET['jtPageSize']);
            $this->m_searchString       = $_SESSION[$nameSearchString];
            $this->m_viewMode           = $_SESSION[$nameViewMode];
           
            if ( $this->m_viewMode == '' ) $this->m_viewMode = 'listAll';
            $action = $_GET['action'];
 
            $sj = "--- _POST ---\n";
            $sj .= pretty_json (json_encode($_POST) );
            $sj .= "--- _GET ---\n";
            $sj .= pretty_json (json_encode($_GET) );
            $sj .= "--- _SESSION ---\n";
            $sj .= pretty_json( json_encode($_SESSION));
            $sj .= "\nmainName: {$this->m_mainName}\nbaseTableName:{$this->m_baseTableName}\nviewMode:'{$this->m_viewMode}'\nsearchString: '{$this->m_searchString}'\n";
        // //     $sj .= pretty_json (json_encode($this->m_dbInterface->getAllTableFields()) );
        // //     $sj .= $ui->pageContents();
            file_put_contents("/tmp/_ajax_dbg.txt", $sj );
            
            // ------------------------------------
            // --- Getting records (listAction) ---
            // ------------------------------------
            if( 'list' == $action )  {
                $this->actionList( $jTableResult, $aData );
            }
            // --------------------------------------------
            // --- Creating a new record (createAction) ---
            // --------------------------------------------
            else if ( 'create' == $action )
            {
                $this->actionCreate( $jTableResult, $aData );
            }
            // ----------------------------------------
            // --- Updating a record (updateAction) ---
            // ----------------------------------------
            else if ( 'update' == $action )
            {
                $this->actionUpdate( $jTableResult, $aData );
            }
            // ----------------------------------------
            // --- Deleting a record (deleteAction) ---
            // ----------------------------------------
            else if ( 'delete' == $action )
            {
                $this->actionDelete( $jTableResult, $aData );
            }
        }
        // ------------------------------
        // --- Other (unknown) errors ---
        // ------------------------------
        catch ( Exception $ex )
        {
            $jTableResult['Result'] = "ERROR";
            $jTableResult['Message'] = $ex->getMessage();
        }
    
        return json_encode($jTableResult);
    }
    
    
    // ------------------------------------------------
    // --- PROTECTED: Ajax handler action functions --- 
    // ------------------------------------------------
    protected function actionList( &$jTableResult, $aData )
    {
        $jTableResult['Result'] = "OK";
        $rows = array();
        if ( 'listAll' == $this->m_viewMode ) {
                $rows   = $this->dbListAll($this->m_listAllStartIndex, $this->m_listAllCount);
                $jTableResult['TotalRecordCount'] = $this->dbCountTotal();
        }
        else if ( 'incrSearch' == $this->m_viewMode  ) {
            $minLen = 3;
            if ( startsWith ( $this->m_searchString, 'the', false ) ) $minLen = 6;

            if ( strlen($this->m_searchString) >= $minLen ) {
                $rows = $this->dbIncrSearch($this->m_searchString);
                $jTableResult['TotalRecordCount'] = count($rows);
            }
        }
        $jTableResult['Records'] = $rows;
    }

    protected function actionCreate( &$jTableResult, $aData )
    {
// 		$sj = "--- actionCreate ---\n";
// 		$sj .= pretty_json (json_encode($aData) );
// 		file_put_contents("/tmp/_ajax_create_dbg.txt", $sj );
        $id = 0;
        $jTableResult['Result'] = 'ERROR';
        if ( $this->dbToID($aData) != 0 ) $jTableResult['Message'] = 'Record already exists';
        else {
            $id = $this->dbCreate($aData);
            if ( $id == 0 ) $jTableResult['Message'] = 'Error creating new record';
            else {
                $aData["{$this->m_baseTableName}_id"] = $id;
                $result = $this->dbUpdateBaseData($aData);
                if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
                else $jTableResult['Result'] = 'OK';
            }
        }
        $row = $this->dbGetBaseData($id);
        $jTableResult['Record'] = $row;
    }

    protected function actionUpdate( &$jTableResult, $aData )
    {
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["{$this->m_baseTableName}_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error updating due to id = 0';
        else {
            $result = $this->dbUpdateBaseData($aData);
            if ( $result < 0 ) $jTableResult['Message'] = 'Error updating fields in new record';
            else $jTableResult['Result'] = 'OK';
        }
        $jTableResult['Result'] = "OK";
    }

    protected function actionDelete( &$jTableResult, $aData )
    {
        $jTableResult['Result'] = 'ERROR';
        $id = $aData["{$this->m_baseTableName}_id"];
        if ( $id == 0 ) $jTableResult['Message'] = 'Error deleting due to id = 0';
        else {
            $result = $this->dbDelete($id);
            if ( $result == 0 ) $jTableResult['Message'] = 'Error deleting record';
            else $jTableResult['Result'] = 'OK';
        }
        $jTableResult['Result'] = "OK";
    }
    
    // -------------------------------------------------------------
    // --- PROTECTED: Ajax handler dbInterface wrapper functions --- 
    // -------------------------------------------------------------
    // Override these in derived class for easy customization

    protected function dbCountTotal()
    {
        return $this->m_dbInterface->getSize();
    }

    protected function dbListAll($startIndex, $count)
    {
        return $this->m_dbInterface->getBaseDataRows($startIndex, $count);
    }

    protected function dbIncrSearch($searchString)
    {
        return $this->m_dbInterface->lookupSimilarBaseData($searchString);
    }

    protected function dbToID($aData)
    {
        return $this->m_dbInterface->toID($aData);
    }
    
    protected function dbCreate($aData)
    {
        return $this->m_dbInterface->newItem($aData);
    }
    
    protected function dbGetBaseData($id)
    {
        return $this->m_dbInterface->getBaseData($id);
    }
    
    protected function dbUpdateBaseData($aData)
    {
        return $this->m_dbInterface->updateBaseData($aData);
    }
    
    protected function dbDelete($id)
    {
        return $this->m_dbInterface->erase($id);
    }
    
    // --------------------------------------
    // --- PRIVATE: Page Helper functions --- 
    // --------------------------------------
    
    public function containerDIV( $width='' )
    {   
        $style = '';
        if ( $width != '' ) $style = "style='width: {$width};'";
        return "<div id='{$this->m_mainName}TableContainer' class='.SimpleTableContainer' {$style} ></div>\n";
    }

    
    
    public function pageScriptSection()
    {
        $sJTableCreateFunction = $this->getJTableCreateFunction("{$this->m_mainName}_jtableCreate");
        $s =
<<<TEXT
    <script type="text/javascript">
        {$sJTableCreateFunction}
        
        {$this->m_mainName}_jtableCreate();
     </script>
TEXT;
        return $s;
    }


    public function getJTableCreateFunction($sFunName = '' )
    {
        $sFunBody = $this->hlpGetJTable();
        if ( '' == $sFunName ) $sFunName = "{$this->m_mainName}_jtableCreate";
        $s =
<<<TEXT
        function $sFunName() {
            {$sFunBody}
            
             $('#{$this->m_mainName}TableContainer').jtable('load');
        };
TEXT;
        return $s;
    }
    
    public function hlpGetJTable()
    {
        $aAllFields     = $this->m_dbInterface->getAllDataFields();
        $aFieldsToList  = $this->getFieldsToList();
        $aFieldsToEdit  = $this->getFieldsToEdit();
        $aFieldsToCreate= $this->getFieldsToCreate();
        
        $s = "\$('#{$this->m_mainName}TableContainer').jtable({\n";
        $s .= $this->hlpGetJTableHeader();
        $s .= $this->hlpGetJTableActions();
        $s .= ',' . $this->hlpGetJTableFields($aAllFields, $aFieldsToList, $aFieldsToEdit, $aFieldsToCreate);
        $s .= "});\n";
        return $s;
    }

    public function hlpGetJTableHeader()
    {   
        $s = "
        title: '{$this->m_mainName}',\n";
        foreach($this->m_aTableOptions as $option => $value ) {
            $s .= "{$option}: '${value}',\n";
        }
 
        $s .= 
<<<TEXT
toolbar: {
    items: [{
        icon: 'css/img/table_reload.png',
        text: 'Reload',
        click: function () {
            $('#{$this->m_mainName}TableContainer').jtable('load');
        }
    }]
},
TEXT;
        
        // sorting: true,
        // defaultSorting: '{$this->m_baseTableName}_name ASC',
        return $s;
    }

    private function hlpGetJTableActions()
    {
        $baseHandlerPath = "ajax_handlers/{$this->m_mainName}_handler.php";
        $s = "actions: {\n";
        $N = count($this->m_aActionsAvailable);
        for ( $i = 0; $i < $N; $i++ ) {
            if ( $i > 0 )   $s .= "\n,  ";
            else            $s .= "   ";
            $actionName = $this->m_aActionsAvailable[$i];
            $s .= "{$actionName}Action: '{$baseHandlerPath}?action={$actionName}{$this->m_extraGETParameters}'";
        }
        $s .= "}\n";
        return $s;
    }
    
    private function hlpGetJTableFields( $aAllFields, $aFieldsToList, $aFieldsToEdit, $aFieldsToCreate )
    {   
        $s = "fields: {\n";

        $N = count($aAllFields);
        for ( $i = 0; $i < $N; $i++ ) {
            if ( $i > 0 ) $s .= ", ";
            $fieldName = $aAllFields[$i];
            $bHide      = !in_array ( $fieldName, $aFieldsToList );
            $bNoEdit    = !in_array ( $fieldName, $aFieldsToEdit );
            $bNoCreate  = !in_array ( $fieldName, $aFieldsToCreate );
            $s .= $this->hlpGetJTableField($fieldName, $i, $bHide, $bNoEdit, $bNoCreate );
        }
        $s .= "}\n";
        return $s;
    }
   
    
    private function hlpGetJTableField( $fieldName, $index, $bHide, $bNoEdit, $bNoCreate )
    {   
        $s = "
            {$fieldName}: {
                title: '{$fieldName}'";

        if ( 0 == $index ) {
            $s .= "
              , key: true
              , create: false
              , edit: false";
        }
        $s .= "\n"; 
        if ( $index < count($this->m_aFieldWidths) ) {
            $w = $this->m_aFieldWidths[$index];
            $s .= ", width: '{$w}%'\n";
        }
        if ( endsWith ( $fieldName, '_date' ) ) {
            $s .= ", type: 'date'";
        }
        if ( $bHide ) {
            $s .= ", list: false";
        }
        if ( $bNoEdit ) {
            $s .= ", edit: false";
        }
        if ( $bNoCreate ) {
            $s .= ", create: false";
        }
        
        
        $s .= "          }\n";
        return $s;
    }

    private function getFieldsToList()
    {
        if ( 0 == count($this->m_aFieldsToList) )    return $this->m_dbInterface->getAllDataFields();
        else                                         return $this->m_aFieldsToList;
    }

    private function getFieldsToEdit()
    {
        if ( 0 == count($this->m_aFieldsToEdit) )    return $this->m_dbInterface->getAllDataFields();
        else                                         return $this->m_aFieldsToEdit;
    }

    private function getFieldsToCreate()
    {
        if ( 0 == count($this->m_aFieldsToCreate) )  return $this->m_dbInterface->getAllDataFields();
        else                                         return $this->m_aFieldsToCreate;
    }
    // ---------------------
    // --- PRIVATE: Data --- 
    // ---------------------
    protected   $m_dbInterface;
    protected   $m_mainName;
    protected   $m_baseTableName;
    protected   $m_aActionsAvailable    = array('list', 'create', 'update', 'delete' );
    protected   $m_aFieldsToList        = array();
    protected   $m_aFieldsToEdit        = array();
    protected   $m_aFieldsToCreate      = array();
    protected   $m_aFieldWidths         = array();
    protected   $m_aTableOptions        = array( 'paging' => 'true' );      
    protected   $m_tableWidth           = '';
    protected   $m_extraGETParameters   = ''; 


    protected   $m_listAllStartIndex    = 0;
    protected   $m_listAllCount         = 0;
    protected   $m_searchString         = '';
    protected   $m_viewMode             = '';
    
}


?>