<?php
namespace App\Http\Controllers\Laboratory;
use App\Http\Controllers\Controller;
use App\Http\Requests\Laboratory\SaveResultsRequest;
use App\Models\LabParameter;
use App\Models\LabResearch;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
final class WorkspaceController extends Controller {
 private function scope(Request $r):Builder {
  $id=(int)$r->user()->laboratory_clinic_id;
  return LabResearch::query()->whereHas('doctor',fn(Builder $q)=>$q->where('clinic_id',$id));
 }
 public function index(Request $r){
  $data=$r->validate(['q'=>['nullable','string','max:100'],'status'=>['nullable','in:ordered,processing,ready']]);
  $q=trim((string)($data['q']??''));$items=$this->scope($r)->with(['patient:id,surname,name,patronym,birth_at','doctor:id,surname,name,patronym']);
  if(!empty($data['status']))$items->where('status',$data['status']);
  if($q!=='')$items->where(function(Builder $b)use($q){
   if(ctype_digit($q))$b->orWhere('id',(int)$q)->orWhere('patient_id',(int)$q);
   $b->orWhereHas('patient',function(Builder $p)use($q){foreach(preg_split('/\s+/u',$q)?:[] as $token)
    $p->where(function(Builder $x)use($token){$x->where('surname','ilike','%'.$token.'%')->orWhere('name','ilike','%'.$token.'%')->orWhere('patronym','ilike','%'.$token.'%');});});
  });
  return view('laboratory.index',['researches'=>$items->orderByDesc('created_at')->paginate(25)->withQueryString()]);
 }
 public function edit(Request $r,int $research){
  $item=$this->scope($r)->with(['patient','doctor','results'])->findOrFail($research);
  $ids=collect((array)$item->parameters)->map(fn($id)=>(int)$id)->filter()->unique()->values();
  $params=LabParameter::whereIn('id',$ids)->get()->keyBy('id');
  $values=$item->results->keyBy('lab_parameter_id')->map(fn($x)=>(string)$x->value);
  return view('laboratory.edit',compact('item','ids','params','values'));
 }
 public function update(SaveResultsRequest $r,int $research){
  $data=$r->validated();
  DB::transaction(function()use($r,$research,$data){
   $item=$this->scope($r)->lockForUpdate()->findOrFail($research);
   abort_unless(in_array($item->status,['ordered','processing'],true),409,'Исследование уже закрыто.');
   $allowed=collect((array)$item->parameters)->map(fn($id)=>(int)$id)->filter()->all();
   foreach($data['results'] as $id=>$value){
    abort_unless(in_array((int)$id,$allowed,true),422);
    $value=trim((string)($value??''));if($value==='')continue;
    $result=$item->results()->where('lab_parameter_id',(int)$id)->first();
    if($result){$result->value=$value;$result->save();}
    else{$item->results()->create(['patient_id'=>$item->patient_id,'lab_parameter_id'=>(int)$id,'value'=>$value]);}
   }
   $item->status=$data['action']==='ready'?'ready':'processing';
   if($data['action']==='ready')$item->result_showed_at=null;
   $item->save();
  });
  return redirect()->route('laboratory.index')->with('success',$data['action']==='ready'?'Результаты переданы врачу.':'Черновик сохранён.');
 }
}
