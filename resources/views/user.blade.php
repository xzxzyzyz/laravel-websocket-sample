<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>Laravel Socket</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link href="https://fonts.googleapis.com/css?family=Lato:100" rel="stylesheet" type="text/css">

    <!-- Bootstrap -->
    <link rel="stylesheet" href="http://maxcdn.bootstrapcdn.com/bootstrap/3.3.5/css/bootstrap.min.css">

    <!-- toastr -->
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/css/toastr.min.css">
</head>
<body>
    <div class="container" id="user">
        <h2>Laravel + Redis + Socket.io + Vue.js</h2>
        <p>Laravel(5.1)でのwebsocketサンプル</p>

        <div class="well">
            <!-- エラー表示 -->
            <div class="alert alert-danger" role="alert" v-if="form.errors">
                <ul>
                    <li v-for="error in form.errors">@{{ error }}</li>
                </ul>
            </div>

            <form class="form-inline" role="form" v-on:submit.prevent="create" autocomplete="off">
                <div class="form-group">
                    <label for="name">Name:</label>
                    <input type="text" class="form-control input-sm" name="name" v-model="form.name">
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" class="form-control input-sm" name="email" v-model="form.email">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" class="form-control input-sm" name="password" v-model="form.password">
                </div>

                <button type="submit" class="btn btn-success btn-sm">Add New User</button>
            </form>
        </div>

        <hr class="">

        <table class="table table-striped table-hover ">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <tr v-for="user in users">
                    <td>@{{ user.id }}</td>
                    <td>@{{ user.name }}</td>
                    <td>@{{ user.email }}</td>
                    <td>
                        <button type="submit" class="btn btn-danger btn-xs" v-on:click="delete(user)">Delete User</button>
                    </td>
                </tr>
            <tr>
            </tbody>
        </table>
    </div>

    <!-- Jquery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
    <!-- Bootstrap -->
    <script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.5/js/bootstrap.min.js"></script>
    <!-- Underscore.js -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/underscore.js/1.8.3/underscore-min.js"></script>
    <!-- Vue.js -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/vue/1.0.14/vue.min.js"></script>
    <!-- toastr -->
    <script src="//cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/js/toastr.min.js"></script>

    {{-- AjaxのHeaderにcsrf-tokenを追加 --}}
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            dataType: 'json'
        });
    </script>

    {{-- toaster初期設定 --}}
    <script>
        toastr.options = {
            "closeButton": false,
            "debug": false,
            "newestOnTop": false,
            "progressBar": false,
            "positionClass": "toast-top-center",
            "preventDuplicates": false,
            "onclick": null,
            "showDuration": "300",
            "hideDuration": "1000",
            "timeOut": "3000",
            "extendedTimeOut": "1000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut"
        }
    </script>

    {{-- レンダリング処理 --}}
    <script>
        var user = new Vue({
            el: '#user',
            data: {
                users: {},
                form: {
                    errors: null,
                    name: '',
                    email: '',
                    password: ''
                }
            },
            created: function() {
                this.get()
                    .done(function(data) {
                        this.setUser(data)
                    }.bind(this))
            },
            methods: {
                create: function() {
                    this.setError(null)
                    this.store()
                        .done(function(data) {
                            this.addUser(data)
                            this.clear()
                        }.bind(this))
                        .fail(function(xhr, status, error) {
                            if (xhr.status === 422) {
                                var response = $.parseJSON(xhr.responseText)
                                this.setError(response)
                            }
                        }.bind(this))
                },
                delete: function(user) {
                    this.destroy(user.id)
                        .done(function(data) {
                            this.delUser(data)
                        }.bind(this))
                },
                store: function() {
                    var defer = $.Deferred();
                    $.ajax({
                        type: 'POST',
                        url: 'api/user',
                        data: {
                            name: this.form.name,
                            email: this.form.email,
                            password: this.form.password
                        },
                        success: defer.resolve,
                        error: defer.reject
                    })

                    return defer.promise();
                },
                get: function() {
                    var defer = $.Deferred();
                    $.ajax({
                        type: 'GET',
                        url: 'api/user',
                        data: {
                            name: this.form.name,
                            email: this.form.email,
                            password: this.form.password
                        },
                        success: defer.resolve,
                        error: defer.reject
                    })

                    return defer.promise();
                },
                destroy: function(id) {
                    var defer = $.Deferred();
                    $.ajax({
                        type: 'POST',
                        url: 'api/user/' + id,
                        data: {
                            _method: 'delete',
                            id: id,
                        },
                        success: defer.resolve,
                        error: defer.reject
                    })

                    return defer.promise();
                },
                setUser: function(user) {
                    this.users = user
                },
                addUser: function(user) {
                    var user_exist = _.find(this.users, function(_user) {
                        return _user.id == user.id
                    })

                    if (! user_exist) {
                        this.users.unshift(user)
                        toastr["success"]("Create user. ID: " + user.id)
                    }
                },
                delUser: function(user) {
                    var user_exist = _.find(this.users, function(_user) {
                        return _user.id == user.id
                    })

                    if (user_exist) {
                        this.users = _.filter(this.users, function(_user) {
                            return _user.id != user.id
                        })
                        toastr["warning"]("Delete user. ID: " + user.id)
                    }
                },
                setError: function(error) {
                    this.form.errors = error
                },
                clear: function() {
                    this.form.name = ''
                    this.form.email = ''
                    this.form.password = ''

                    setTimeout(function() {
                        $('input[name=name]').focus()
                    }, 100)
                }
            }
        })
    </script>

    {{-- ソケット通信処理 --}}
    <script src="https://cdn.socket.io/socket.io-1.3.4.js"></script>
    <script>
        var socket = io.connect('{{ config('app.url') }}:8890');
        socket.on('channel-users:store', function (message) {
            user.addUser(message.user)
        })
        socket.on('channel-users:destroy', function (message) {
            user.delUser(message.user)
        })
    </script>
</body>
</html>
