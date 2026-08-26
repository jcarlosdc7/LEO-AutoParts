@props(['id' => null, 'maxWidth' => null])

<x-modal :id="$id" :maxWidth="$maxWidth" {{ $attributes }}>

	<div class="border-b border-slate-100 px-5 pb-4 pt-5 text-lg font-medium text-gray-900 dark:border-slate-800 sm:px-6 sm:pt-6">
		{{ $title }}
	</div>

	<div class="px-5 py-5 text-sm text-gray-600 sm:px-6 sm:py-6">
		{{ $content }}
	</div>

	<div class="flex flex-wrap justify-end gap-2 border-t border-slate-200 bg-gray-100 px-5 py-4 text-end dark:border-slate-800 sm:px-6">
		{{ $footer }}
	</div>
</x-modal>
