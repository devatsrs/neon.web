<tr id="selectedRow-0">
    <td>
        {{ Form::select('Component-0', RateGenerator::$Component,  RateGenerator::$Component , array("class"=>"select2" ,'multiple')) }}

    </td>
    <td>
        {{ Form::select('Action-0', RateGenerator::$Action, RateGenerator::$Action , array("class"=>"select2")) }}

    </td>
    <td>
        {{ Form::select('MergeTo-0', RateGenerator::$Component,  RateGenerator::$Component , array("class"=>"select2" ,'multiple')) }}


    </td>
    <td>
        <button type="button" onclick="createCloneRow()" id="Service-update" class="btn btn-primary btn-sm add-clone-row-btn" data-loading-text="Loading...">
            <i></i>
            +
        </button>
        <a onclick="deleteRow(this.id)" id="delete-0" class="btn delete btn-danger btn-sm" data-loading-text="Loading...">
            <i></i>
            -
        </a>
    </td>
</tr>