@extends('layouts/ecl')


@if($platform)
    @section('title', 'Data Download for ' . $platform->name)
@else
    @section('title', 'Data Download')
@endif

@section('breadcrumbs')
    @if($platform)
        <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
        <x-ecl.breadcrumb label="Data Download" url="{{ route('dayarchive.index') }}" />
        <x-ecl.breadcrumb :label="$platform->name"/>
    @else
        <x-ecl.breadcrumb label="Home" url="{{ route('home') }}" />
        <x-ecl.breadcrumb label="Data Download" />
    @endif
@endsection


@section('content')

    <div class="ecl-fact-figures ecl-fact-figures--col-1">
        <div class="ecl-fact-figures__description">
            On this page, you can download zip files containing the daily submissions of statements of reasons,
            either for all platforms collectively or for each platform individually. The files are provided in two
            different versions: full and light.<br />
            <br />
            Full archive files contain all of the public data points of each individual statement of reasons
            submitted on a given day, i.e. each file contains the entire attribute schema of the database.<br />
            <br />
            Light archive files do not contain free text attributes with a character limit higher than 2000
            characters, i.e. they do not contain the attributes illegal_content_explanation,
            incompatible_content_explanation & decision_facts. Light archive files also do not contain the
            territorial_scope attribute. The archiving feature is currently in a beta version and the file structure
            might change in future iterations, with additional file formats being considered as well.
        </div>
    </div>

    <div class="ecl-row ecl-u-mt-l">
        <div class="ecl-col-l-6">
            @if($platform)
                <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Daily Archives for {{ $platform->name }}</h1>
            @else
                <h1 class="ecl-page-header__title ecl-u-type-heading-1 ecl-u-mb-l">Data Download</h1>
            @endif

        </div>

        <div class="ecl-col-l-6">
            <form method="get" id="platform">
                <x-ecl.select label="Select a Platform" name="uuid" id="uuid"
                              justlabel="true"
                              :options="$options['platforms']" :default="request()->route('uuid')"
                />
            </form>
            <script>
              var uuid = document.getElementById('uuid')
              uuid.onchange = (event) => {
                document.location.href = '{{ route('dayarchive.index') }}/' + event.target.value
              }
            </script>
        </div>
    </div>

    <x-dayarchive.table :dayarchives="$dayarchives"/>

@endsection
