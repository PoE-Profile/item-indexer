@extends('layouts.app')

@section('jsData')

<script type="text/javascript">
  window.PHP = {
    csrf_token: "{{ csrf_token() }}"
  }
</script>

@stop

@section('script')
<script src="http://unpkg.com/vue@2.1.10"></script>
<script type="text/javascript">

new Vue({
  el: '#app',
  data:{
    mods: [],
  },
  methods: {
    addMod: function () {
        var index=this.mods.length;
        this.mods.push({
            mod_id:'mods['+index+'][mod_id]',
            min:'mods['+index+'][min]',
            max:'mods['+index+'][max]',
        })
        Vue.nextTick(function () {
            $(".chosen-select").chosen({no_results_text: "Oops, nothing found!",search_contains:true});
        });
    }
  }
});

$( document ).ready(function() {
    $(".chosen-select").chosen({no_results_text: "Oops, nothing found!",search_contains:true});
});

</script>

@stop

@section('content')
{!! Form::open(['url' => '/']) !!}

<div class="row" style="padding-bottom: 20px;padding-left: 15px;padding-right: 15px">
    <label>Item Name</label>
    <input type="text" class="form-control" name="name" value="">
</div>

<div class="row" id="app">

    <div class="col-lg-2">
        <select class="chosen-select" name="type" >
            <option value="0">select</option>
            @foreach ($bigData->TypeAndBaseType as $object)
            <option value="{{$object->type}}">{{$object->type}}</option>
            @endforeach
        </select>
    </div>

    <div class="col-lg-4">
        <label>Base</label>

        <select class="chosen-select" name="typeLine" >
            <option value="0">select</option>
            @foreach ($bigData->TypeAndBaseType as $object)
                <optgroup label="{{$object->type}}">
                    @foreach ($object->baseTypes as $type)
                    <option value="{{$type}}">{{$type}}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>


    <div class="col-lg-3">
        <label>Rarity</label>
        <select name="rarity">
            <option value="0">Normal</option>
            <option value="1">Magic</option>
            <option value="2">Rare</option>
            <option value="3">Unique</option>
            <option value="4">Gem</option>
            <option value="5">Currency</option>
        </select>
    </div>


    <div class="col-lg-3">
        <label>League</label>
        <select name="league">
            <option value="Bestiary">Bestiary</option>
            <option value="Hardcore Bestiary">Hardcore Bestiary</option>
        </select>
    </div>

</div>

<br>

<div class="row">

    <div class="col-lg-2" style="padding-bottom: 30px;">
        <label>Sockets</label>
        <ul class="itemLevel">
            <li style="padding-right: 5px;"><input type="text" name="socket_min" class="num smInputs" placeholder="min" width="50" style="width: 50px"></li>
            <li ><input type="text" name="socket_max" class="num smInputs" placeholder="max" width="50" style="width: 50px"></li>
        </ul>
    </div>

    <div class="col-lg-2" style="padding-bottom: 30px;">
        <label>Links</label>
        <ul class="itemLevel">
            <li style="padding-right: 5px;"><input type="text" name="links_min" class="num smInputs" placeholder="min" width="50" style="width: 50px"></li>
            <li ><input type="text" name="links_max" class="num smInputs" placeholder="max" width="50" style="width: 50px"></li>
        </ul>
    </div>

    <div class="col-lg-2">
        <label>ItemLevel</label>
        <ul class="itemLevel">
            <li style="padding-right: 5px;"><input type="text" name="level_min"class="num smInputs" placeholder="min" width="50" style="width: 50px"></li>
            <li ><input type="text" name="level_max"  class="num smInputs" placeholder="max" width="50" style="width: 50px"></li>
        </ul>
    </div>
    <div class="col-lg-2">
        <label>Corrupted</label><br>
        <select name="corrupted">
            <option value="">select</option>
            <option value="0">Not Corrupted</option>
            <option value="1">Corrupted</option>
        </select>
    </div>
    <div class="col-lg-2">
        <input type="checkbox" name="buyout" class="buyout"> Buyout Only<br>
    </div>

</div>

<div class="row" v-for="(mod, index) in mods">
    <div class="col-lg-1">mod @{{index+1}}:</div>
    <div class="col-lg-8">
        <select class="chosen-select" :name="mod.mod_id" >
            @foreach ($bigData->mods as $mod)
            <option value="{{$mod->id}}">{{$mod->name}}</option>
            @endforeach
        </select>
    </div>
    <div class="col-lg-3">
        <ul class="itemLevel">
            <li style="padding-right: 10px;">
                <input style="height: 30px;width: 120px; text-align: center" type="text" :name="mod.min" id="mod_min" class="num" placeholder="min">
            </li>
            <li >
                <input style="height: 30px;width: 120px; text-align: center" type="text" :name="mod.max" id="mod_max" class="num" placeholder="max">
            </li>
        </ul>
    </div>
</div>

<div>
    <a href="#" class="sub btn btn-default btn-sm form-control" v-on:click.prevent="addMod">add mod</a>
    <br><br>
</div>
<button type="submit" class="btn btn-primary form-control">Search</button>

<br><br>


</form>

{!! Form::close() !!}

<div class="container col-xs-12 col-sm-12 col-lg-12 main-content">
  @if (isset($resultItems))
    <div class="list-group">
        @each('components.item', $resultItems, 'item')
    </div>
  @endif
</div>


@stop
