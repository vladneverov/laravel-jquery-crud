@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">Все записи</div>
                <div class="card-body">

                    <a class="btn btn-success mb-3" id="createNewPost"
                       href="javascript:void(0)">Создать запись</a>

                    <table class="data-table table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">id</th>
                                <th scope="col">Заголовок</th>
                                <th scope="col">Описание</th>
                                <th scope="col">Дата создания</th>
                                <th scope="col"></th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                    <div class="modal fade" id="ajaxModel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h4 class="modal-title" id="modelHeading"></h4>
                                <button type="button" class="close"
                                        data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body">

                                <div class="alert alert-danger print-error-msg" style="display:none">
                                    <ul></ul>
                                </div>

                                <form id="postForm" name="postForm" class="form-horizontal">
                                    <input type="hidden" name="id" id="id">

                                    <div class="form-group">
                                        <label for="title" class="col-sm-3 control-label">Заголовок</label>
                                        <div class="col-sm-12">
                                            <input type="text" class="form-control"
                                                   id="title" name="title" maxlength="40" required>
                                        </div>
                                    </div>
                        
                                    <div class="form-group">
                                        <label class="col-sm-3 control-label">Описание</label>
                                        <div class="col-sm-12">
                                            <textarea name="description" id="description"
                                                      class="form-control"
                                                      rows="4" maxlength="110" required></textarea>
                                        </div>
                                    </div>
                        
                                    <div class="col-sm-offset-2 col-sm-10">
                                        <button type="submit" class="btn btn-primary"
                                                id="saveBtn" value="create">Сохранить</button>
                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Закрыть</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
$(function() {

    // Загрузка таблицы
    var table = $('.data-table').DataTable({
        language: {
            search: 'Поиск',
            lengthMenu: 'Показать _MENU_',
            info: 'Отображение _START_ по _END_ из _TOTAL_ записей',
            infoEmpty: 'Показано с 0 по 0 из 0 записей',
            infoFiltered: '(отфильтровано по итоговым записям _MAX_)',
            processing: 'Подождите...',
            emptyTable: 'Данные отсутствуют в таблице',
            loadingRecords: 'Загрузка...',
            zeroRecords: 'Записи не найдены',
            paginate: {
                first: 'Первый',
                last: 'Последний',
                next: 'Вперед',
                previous: 'Назад'
            },
            aria: {
                sortAscending: ': активировать для сортировки столбцов по возрастанию',
                sortDescending: ': активировать для сортировки столбца по убыванию'
            }
        },
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('posts.index') }}",
            type: "GET"
        },
        columns: [
            { data: 'id', name: 'index' },
            { data: 'title', name: 'title' },
            { data: 'description', name: 'description' },
            { data: 'created_at', name: 'created_at' },
            { data: 'action', name: 'action', orderable: false, searchable: false },
        ],
        columnDefs: [
            { width: '23%', targets: 3 },
            { width: '13%', targets: 4 }
        ],
    });

    // Валидация
    var validator = $('#postForm').validate({
        errorClass: 'is-invalid',
        rules: {
            title: {
                required: true,
                maxlength: 30
            },
            description: {
                required: true,
                maxlength: 100
            }
        },
        messages: {
            title: {
                required: "Заголовок обязательно для заполнения",
                maxlength: jQuery.validator.format("Допустимо максимум {0} символов"),
            },
            description: {
                required: "Описание обязательно для заполнения",
                maxlength: jQuery.validator.format("Допустимо максимум {0} символов"),
            }
        }
    });

    function clearValidateInputs() {
        $('#postForm .is-invalid').each(function() {
            $(this).removeClass("is-invalid");
        });
    }
     
    // Создать запись
    $('#createNewPost').click(function()
    {
        validator.resetForm()
        clearValidateInputs()

        $('#saveBtn').val("Создать запись");
        $('#id').val('');
        $('#postForm').trigger("reset");
        $('#modelHeading').html("Создать запись");
        $('#ajaxModel').modal('show');
    });
    
    // Редактировать запись
    $('body').on('click', '.editPost', function()
    {
      validator.resetForm()
      clearValidateInputs()

      var id = $(this).data('id');

      $.get(`{{ route('posts.index') }}/${id}/edit`, function (post)
      {
          $('#modelHeading').html("Редактировать");
          $('#saveBtn').val("edit-user");
          $('#ajaxModel').modal('show');
          $('#id').val(post.id);
          $('#title').val(post.title);
          $('#description').val(post.description);
      })
   });
    
    $('#saveBtn').click(function(e)
    {
        $(this).html('Отправка..');
    
        $.ajax({
          data: $('#postForm').serialize(),
          url: "{{ route('posts.store') }}",
          type: "POST",
          dataType: 'json',
          success: function (data)
          {
              $('#postForm').trigger("reset");
              $('#ajaxModel').modal('hide');
              $('#saveBtn').html('Сохранить');
              table.draw();
          },
          error: function (data)
          {
              console.log('Error:', data);
              $('#saveBtn').html('Сохранить');
          }
      });
    });
    
    // Удалить запись
    $('body').on('click', '.deletePost', function()
    {
        var id = $(this).data("id");
      
        $.ajax({
            url: `{{ route('posts.store') }}/${id}`,
            type: "DELETE",
            success: function (data) {
                table.draw();
            },
            error: function (data) {
                console.log('Error:', data);
            }
        });
    });
     
  });
</script>
@endpush