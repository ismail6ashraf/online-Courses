@extends('layouts.app')

@section('title', 'Create Courses')
@section('page-title', 'Create New Course')

@section('content')

    <div class="card">
        <div class="card-body">

            <form action="{{ route('instructor.courses.store') }}" method="POST">
                @csrf
                <input type="hidden" name="status" value="active">
                <div class="mb-3">
                    <label class="form-label">Course Title</label>

                    <input type="text" name="title" class="form-control" placeholder="Course Title">
                </div>

                <div class="mb-3">
                    <label class="form-label">Description</label>

                    <textarea name="description" class="form-control" rows="4"></textarea>
                </div>

                <button type="submit" class="btn btn-primary">
                    Create Course
                </button>

            </form>

        </div>
    </div>

@endsection
