@extends(repeat_layout())



@section('content')


    <div class="card h-100">

        <div class="card-title p-3">
            <div class="d-flex align-items-center">
                <div class="bg-primary text-white p-3 me-3 rounded-2">
                  <span class="">
                    <span class="{{\RepeatToolkit\Helpers\StaticHelpers\HtmlHelper::getModelIcon($view_obj->table_name)}} fs-2"></span>
                  </span>
                </div>
                <div>
                    <div class="fs-6 fw-semibold text-primary"><span class="badge bg-secondary mb-2 opacity-10">{{$view_obj->model->html_subtitle ?? ""}}</span>
                    </div>
                    <div class="text-body-secondary text-uppercase fw-semibold small fs-4">{{$view_obj->model->html_title ?? ""}}</div>
                </div>
            </div>
        </div>

        <div class="card-body">
            <ul class="nav nav-tabs mb-3">
                @foreach ($view_obj->slugs as $slug => $label)
                    <li class="nav-item">
                        <a class="nav-link {{ $slug === $view_obj->slug ? 'active' : '' }}"
                           href="{{ route($view_obj->table_name . ".create_partial", [$slug, $view_obj->model->id ?? ""]) }}">
                            {{ strtoupper($label) }}
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="mt-3">
                <{{$view_obj->table_name}}-{{$view_obj->slug}}-form
                    @if($view_obj->model != null)
                    model='@json($view_obj->model ?? null)'
                    @endif

                @if(isset($view_obj->json_data))
                    @foreach($view_obj->json_data as $key => $data)

                        {{$key}}='@json($data)'

                    @endforeach
                @endif
                >
                </{{$view_obj->table_name}}-{{$view_obj->slug}}-form>
            </div>
        </div>
    </div>

@endsection
