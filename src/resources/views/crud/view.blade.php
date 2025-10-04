@extends(repeat_layout())



@section('content')



    <data-table user_id="{{\Illuminate\Support\Facades\Auth::user()->id ?? ""}}" table_name="{{$view_obj->table_name ?? ""}}" src="{{$view_obj->route}}"></data-table>

@endsection
