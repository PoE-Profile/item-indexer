
<div class="row">
 <div class="col-xs-2">
   <br><br>
   <div class="icon">
     <div class="sockets" style="position: absolute;">
       <div class="socket-inner" style="position: relative; width: 94px;">
         @if ($item->sockets)
           <?php
             $lastSocket = '';
             $countSock = 0;
           ?>

           @foreach ($item->sockets as $socket)

             <div class="socket socket{{$socket->attr}} {{($countSock === 2 or $countSock === 3) ? 'socketRight' : ''}}"></div>
             @if ($lastSocket === (int)$socket->group)
               <div class="socketLink socketLink{{$countSock - 1}}"></div>
             @endif
             <?php
               $lastSocket = (int)$socket->group;
               $countSock++;
             ?>
           @endforeach
         @endif
       </div>
     </div>
     <img style="-webkit-user-select: none" src="{{$item->icon}}">

   </div>

 </div>
 <div class="col-xs-6" style="" class="text-center">
   <?php
   $color = ['white', 'blue', 'yellow', 'orange', '#c9c9c9', 'white', 'white', 'white', 'white', 'white']
   ?>
   <h2 style="margin-top: 0px;margin-bottom:0px;color:{{$color[$item->frameType]}};">
       {{$item->name}} {{$item->typeLine}}(frameType:{{$item->frameType}})
   </h2>
   @if ($item->requirements)
   <p style="color: #c1c1c1;">
       @foreach ($item->requirements as $requirement)
       {{$requirement['name']}}:{{$requirement['values'][0][0]}}
       @endforeach
   </p>
   @endif
   @if ($item->corrupted)
     <p style="color:red;">corrupted</p>
   @endif
   @if($item->implicitMods)
   <p class="mods">
       @foreach ($item->implicitMods as $imp)
       {{$imp}}<br>
       @endforeach
   </p>
   @endif
   <hr>
   <p class="mods">
     @if($item->explicitMods)
       @foreach ($item->explicitMods as $expl)
       {{$expl}}<br>
       @endforeach
     @endif
   </p>
 </div>
 <div class="col-xs-4" style="color: #999999;">
   <!-- <h2>other stats</h2> -->
   <p> IGN: {{$item->stash->lastCharacterName}}</p>
   <p> Price: {{$item->note}}</p>
   <p> ItemLevel: {{$item->ilvl}}</p>
   <p> League: {{$item->league}}</p>
   <!-- {{dump($item)}} -->
 </div>
</div>

<hr>
