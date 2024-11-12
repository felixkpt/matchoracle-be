@component('mail::message')
# Introduction

The body of your message.
![](https://calltronix.co.ke/frontend/assets/img/about-us.png)

@component('mail::button', ['url' => ''])
View
@endcomponent
@php
    $arrays =[
        [
        'name'=>'ian',
        'email'=>'iand@gmail.com',
        'phone'=>071233434
    ],
    [
        'name'=>'ian',
        'email'=>'iand@gmail.com',
        'phone'=>071233434
    ],[
        'name'=>'ian',
        'email'=>'iand@gmail.com',
        'phone'=>071233434
    ]
    ];
@endphp

@component('mail::table')
@php
$arrays =[
    [
    'name'=>'john',
    'email'=>'johndoe@gmail.com',
    'phone'=>071233434
],
[
    'name'=>'ian',
    'email'=>'johndoe@gmail.com',
    'phone'=>071233434
],[
    'name'=>'ian',
    'email'=>'johndoe@gmail.com',
    'phone'=>071233434
]
];
@endphp
| Name       | Email         | Phone  |
| ------------- |:-------------:| --------:|
@foreach($arrays as $arr)
| {{$arr['name']}}      | {{$arr['email']}}        | {{$arr['phone']}}      |
@endforeach
@endcomponent
@component('mail::panel')
Panel Text
@endcomponent
Thanks,<br>
{{ config('app.name') }}
@endcomponent
