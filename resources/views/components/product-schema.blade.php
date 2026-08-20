@props(['product'])

<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@type": "Product",
  "name": "{{ $product->name }}",
  "image": "{{ $product->image_url }}",
  "description": "{{ Str::limit(strip_tags($product->description), 150) }}",
  "sku": "{{ $product->sku ?? $product->id }}",
  "brand": {
    "@@type": "Brand",
    "name": "{{ $product->brand->name ?? 'Default Brand' }}"
  },
  "offers": {
    "@@type": "Offer",
    "url": "{{ url()->current() }}",
    "priceCurrency": "IRR",
    "price": "{{ $product->price }}",
    "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "itemCondition": "https://schema.org/NewCondition"
  }
}
</script>
