<flux:sidebar.nav>
    @foreach($this->categories as $category)
        <flux:sidebar.item :icon="$category->icon" href="#">{{ $category->name }}</flux:sidebar.item>
    @endforeach

</flux:sidebar.nav>
