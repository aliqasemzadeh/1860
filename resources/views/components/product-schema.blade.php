@props(['product'])

<script type="application/ld+json">
{
  "@@context": "https://schema.org/",
  "@@type": "Product",
  "name": "{{ $product->name }}",
  "image": "{{ $product->image_url }}",
  "description": "{{ Str::limit(strip_tags($product->description), 150) }}",
  "sku": "{{ $product->sku }}",
  "category": "{{ $product->category_name }}",
  "brand": {
    "@@type": "Brand",
    "name": "{{ $product->brand_name }}"
  },
  "offers": {
    "@@type": "Offer",
    "url": "{{ url()->current() }}",
    "priceCurrency": "IRR",
    "price": "{{ $product->price }}",
    "priceValidUntil": "{{ date('Y-m-d', strtotime('+1 month')) }}",
    "availability": "{{ $product->stock > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock' }}",
    "itemCondition": "https://schema.org/NewCondition",
    "hasMerchantReturnPolicy": {
      "@@type": "MerchantReturnPolicy",
      "applicableCountry": "IR",
      "returnPolicyCategory": "https://schema.org/MerchantReturnFiniteReturnWindow",
      "merchantReturnDays": 7,
      "returnMethod": "https://schema.org/ReturnByMail",
      "returnFees": "https://schema.org/FreeReturn"
    },
    "shippingDetails": {
      "@@type": "OfferShippingDetails",
      "shippingRate": {
        "@@type": "MonetaryAmount",
        "value": "0",
        "currency": "IRR"
      },
      "shippingDestination": {
        "@@type": "DefinedRegion",
        "addressCountry": "IR"
      },
      "deliveryTime": {
        "@@type": "ShippingDeliveryTime",
        "handlingTime": { "@@type": "QuantitativeValue", "minValue": 0, "maxValue": 1, "unitCode": "d" },
        "transitTime": { "@@type": "QuantitativeValue", "minValue": 1, "maxValue": 3, "unitCode": "d" }
      }
    }
  }
}
</script>
