@extends('lms.layouts.app')

@section('title', 'LMS Course Materials — Select Class')
@section('page-header', 'Subject Materials & RAG Document Uploads')

@section('content')
<style>
    .lms-hero {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 18px;
        padding: 28px 32px;
        position: relative;
        overflow: hidden;
        box-shadow: none;
    }
    .class-card {
        background: #F9F8F5;
        border: 1px solid #E1DFD7;
        border-radius: 16px;
        transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
        cursor: pointer;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        display: block;
        box-shadow: none;
    }
    .class-card:hover {
        transform: translateY(-3px);
        border-color: #D48A2E;
        box-shadow: 0 10px 24px rgba(212, 138, 46, 0.12);
    }
    .class-card-header {
        padding: 22px 24px 0;
    }
    .class-card-body {
        padding: 16px 24px;
    }
    .class-card-footer {
        padding: 14px 24px 18px;
        border-top: 1px solid #EAE8E1;
        background: #F4F3EE;
    }
    .class-icon {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 20px;
        font-weight: 800;
        flex-shrink: 0;
        background: #17191C;
        color: #F0B45D;
        border: 1px solid #2A2C30;
    }
    .stat-pill {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 10px;
        border-radius: 8px;
        font-size: 11.5px;
        font-weight: 700;
    }
    .class-card .arrow-indicator {
        transition: transform 0.2s ease, color 0.2s ease;
        color: #8A877E;
    }
    .class-card:hover .arrow-indicator {
        transform: translateX(4px);
        color: #D48A2E;
    }
</style>

<div class="space-y-6">
    <!-- Hero Section -->
    <div class="lms-hero">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px;">
            <div>
                <h1 style="font-family: 'Outfit', sans-serif; font-size: 22px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">
                    📚 LMS Course Materials &amp; RAG Library
                </h1>
                <p style="font-size: 13.5px; color: #68665D; margin: 0; max-width: 620px; line-height: 1.5;">
                    Select a class below to view and manage subject materials. Upload PDF textbooks and Word notes for AI-powered RAG indexing.
                </p>
            </div>
            @if(isset($activeTerm))
                <div class="stat-pill" style="background: #F8E9D3; border: 1px solid #E8CEAA; color: #8A5A10;">
                    <x-icon name="calendar-check" class="w-3.5 h-3.5" /> Active: {{ $activeTerm->name ?? 'Current Term' }}
                </div>
            @endif
        </div>
    </div>

    <!-- Classes Grid -->
    @if($classes->isEmpty())
        <div style="text-align: center; padding: 64px 24px; background: #F9F8F5; border: 1px dashed #E1DFD7; border-radius: 16px;">
            <div style="display: inline-flex; width: 64px; height: 64px; border-radius: 14px; align-items: center; justify-content: center; font-size: 28px; margin-bottom: 14px; background: #F2EFEB; border: 1px solid #E1DFD7; color: #8A877E;">
                <x-icon name="building-columns" class="w-7 h-7" />
            </div>
            <h3 style="font-size: 17px; font-weight: 800; color: #1B1A17; margin: 0 0 6px;">No Classes with Subjects Found</h3>
            <p style="font-size: 13px; color: #68665D; max-width: 420px; margin: 0 auto; line-height: 1.5;">
                There are no classes with registered subjects in the active academic term. Configure subjects from Academic Suite.
            </p>
        </div>
    @else
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(340px, 1fr)); gap: 18px;">
            @foreach($classes as $i => $class)
                @php
                    $sectionsCount = $class->sections->count();
                @endphp
                <a href="{{ route('lms.subjects.class', $class->id) }}" class="class-card">
                    <div class="class-card-header">
                        <div style="display: flex; align-items: flex-start; gap: 14px;">
                            <div class="class-icon">
                                {{ strtoupper(substr($class->name, 0, 1)) }}
                            </div>
                            <div style="flex: 1; min-width: 0;">
                                <h3 style="font-family: 'Outfit', sans-serif; font-size: 16.5px; font-weight: 800; color: #1B1A17; margin: 0 0 3px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    {{ $class->name }}
                                </h3>
                                <div style="font-size: 11.5px; color: #68665D; font-weight: 600;">
                                    @if($class->systemClass)
                                        {{ $class->systemClass->name }} •
                                    @endif
                                    {{ $sectionsCount }} {{ Str::plural('Section', $sectionsCount) }}
                                </div>
                            </div>
                            <div class="arrow-indicator" style="font-size: 16px; margin-top: 2px;">→</div>
                        </div>
                    </div>
                    <div class="class-card-body">
                        <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap;">
                            <span class="stat-pill" style="background: #F8E9D3; color: #8A5A10; border: 1px solid #E8CEAA;">
                                <x-icon name="book-bookmark" class="w-3.5 h-3.5" /> {{ $class->subjects_count }} {{ Str::plural('Subject', $class->subjects_count) }}
                            </span>
                            <span class="stat-pill" style="background: #EAE8E1; color: #68665D; border: 1px solid #E1DFD7;">
                                <x-icon name="file-lines" class="w-3.5 h-3.5" /> {{ $class->materials_count ?? 0 }} {{ Str::plural('File', $class->materials_count ?? 0) }} Uploaded
                            </span>
                        </div>
                    </div>
                    <div class="class-card-footer">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="flex: 1; height: 6px; background: #E1DFD7; border-radius: 999px; overflow: hidden;">
                                @php
                                    $maxFiles = max($class->subjects_count * 3, 1);
                                    $progress = min(($class->materials_count ?? 0) / $maxFiles * 100, 100);
                                @endphp
                                <div style="height: 100%; width: {{ $progress }}%; background: #D48A2E; border-radius: 999px; transition: width 0.4s ease;"></div>
                            </div>
                            <span style="font-size: 11px; color: #8A5A10; font-weight: 800; white-space: nowrap;">
                                {{ round($progress) }}% coverage
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    @endif
</div>
@endsection
