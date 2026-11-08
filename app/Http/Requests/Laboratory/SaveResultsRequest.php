<?php
namespace App\Http\Requests\Laboratory;
use App\Models\LabResearch;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
final class SaveResultsRequest extends FormRequest {
 public function authorize():bool {
  $u=$this->user(); $item=LabResearch::find($this->route('research'));
  return (bool)($u && method_exists($u,'hasRole') && $u->hasRole('laborant') && $u->laboratory_clinic_id && $item
    && $item->doctor()->where('clinic_id',$u->laboratory_clinic_id)->exists()
    && in_array($item->status,['ordered','processing'],true));
 }
 public function rules():array {return ['action'=>['required','in:draft,ready'],'results'=>['required','array','max:300'],'results.*'=>['nullable','string','max:255']];}
 public function after():array {return [function(Validator $v):void {
  $item=LabResearch::find($this->route('research'));if(!$item)return;
  $allowed=collect((array)$item->parameters)->map(fn($x)=>(string)(int)$x)->all();
  $values=(array)$this->input('results',[]);
  foreach($values as $id=>$value)if(!in_array((string)$id,$allowed,true)){$v->errors()->add('results','Показатель отсутствует в назначении.');break;}
  if($this->input('action')==='ready')foreach($allowed as $id)if(trim((string)($values[$id]??''))==='')$v->errors()->add('results.'.$id,'Заполните показатель.');
 }];}
}
