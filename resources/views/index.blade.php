@extends('layouts.app')

@section('jsData')

<script type="text/javascript">
  window.PHP = {
    oldFields: {!! json_encode($oldFields) !!},
    userAcc: '{!! $userAcc !!}',
    csrf_token: "{{ csrf_token() }}"
  }
</script>

@stop

@section('script')
<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/clipboard.js/1.5.12/clipboard.min.js"></script>
<script type="text/javascript" src="/js/build/main.js"></script>
 <script type="text/javascript">

  $( document ).ready(function() {
    // on hover item tooltip
    $('.itemtooltip').tooltip({
      title:"Click to see compere in Sidebar",
      placement:"top"
    });
    new Clipboard('.clipboard');


  });



</script>

@stop


@section('content')

  <div class="row row-offcanvas row-offcanvas-right" >

    <div class="col-sm-4 col-lg-3 sidebar sidebar-offcanvas" v-if='accountCharacters !== ""' v-cloak>

      <div class="panel panel-default">

        <div class="panel-heading">Chouse Character:</div>
        <div class="list-group" v-show="showChars" v-cloak>
          <p href="#"
            class="list-group-item"
            v-for="(index, char) in accountCharacters "
          >

            <a href=""  @click.prevent="showProfile(index)"> @{{char.name}}
            </a>

            <small class="pull-right"> @{{ char.league }}</small>
          </p>
        </div>

        <div class="panel-heading">Character:</div>
        <div class="panel-body" v-show="character !== ''" v-cloak>
          <div class="character" >
            <div class="icon @{{character.class}}"></div>
            <div class="infoLine1"><span class="characterName">@{{character.name}}</span></div>
            <div class="infoLine2">Level @{{character.level}} @{{character.class}}</div>
            <div class="infoLine3">@{{character.league}} League</div>
          </div>

          <div class="char-menu">
            <a href="#" class="btn btn-success btn-sm" style="margin-right: 5px;">
                <i class="glyphicon glyphicon-repeat"></i>
            </a>
            <a href="#"
              class="btn btn-success btn-sm"
              style="margin-right: 5px;"
              @click.prevent="hideProfile"
            >
                Change
            </a>
          </div>
        </div>

        <div class="panel-heading">Comperison:</div>
        <div class="list-group" v-show="managerResult" v-cloak>
          <div class="list-group-item" v-for="mod in managerResult">
            <p v-bind:style="{ color: mod.color}">
              @{{ (mod.diff > 0) ? '+'+mod.diff : mod.diff}} : @{{mod.name}} <span>( Char Item:@{{mod.value}} )</span>
            </p>
          </div>
        </div>

        <div class="panel-heading">Marked Items:</div>
        <div class="list-group" v-show="markedItems" v-cloak>
          <p href="#"
            class="list-group-item"
            v-for="(index, marked) in markedItems"
          >
            <a href="#@{{marked.itemId}}"> @{{marked.name}} </a>
            <a class="pull-right" href="#" style="color:red;" @click.prevent="removeMarked(index, marked.itemId)"> Remove </a>

          </p>
        </div>

        <div class="panel-heading">Items:</div>
        <div class="list-group" v-show="charItems" v-cloak>
          <div class="list-group-item" v-for="(index, item) in character.items " @click.prevent="showMods(item)">
            <a href=""> @{{index}} </a>
          </div>
        </div>

        <div class="list-group" v-show="!charItems">
          <div class="list-group-item">
            <a href="#"  class="pull-right btn btn-success btn-sm"  @click.prevent="showItems()"> change </a> <br>
          </div>
          <div class="list-group-item" v-for="mod in itemMods" v-cloak>
              @{{mod}}
          </div>
        </div>
      </div>
    </div><!--/.sidebar-offcanvas-->

    <div v-bind:class="['container', (accountCharacters !== '') ? 'col-xs-12 col-sm-8 col-lg-9 main-content' : '']">

      <div class="searching" style="padding-bottom: 100px">
        <form  method="get" action="{{route('queries.search')}}"  id="searchform">
          <main-form :big-data="{{$bigData}}" :uniq-names="{{$uniqNames}}"><main-form>
        </form>
      </div>

      @if ($searchItem && isset($searchItem))
        <div class="list-group">
            @foreach ($searchItem as $key => $item)
              <div v-bind:class="[itemClass.default, isMarked({{$item}}) ? itemClass.marked : '']" id="{{$item->itemId}}">
                <a href=""
                  v-show="userAcc"
                  style="padding-right: 5px"
                  class="btn btn-danger btn-sm pull-right"
                  @click.prevent="markItem({{$item}})" v-cloak
                > pin </a>
                <item :item-stash="{{$item->stash}}" :item="{{$item}}"  item-id="{{$key}}"@click.prevent="compereItem({{$item}})"></item>
              </div>
            @endforeach
        </div>

      @endif
    </div><!--/.main-->

  </div>

@stop
