# Agentic Commerce for Magento 2

The Agentic Commerce Protocol (ACP) is an open standard that enables a conversation between buyers, their AI agents, and businesses to complete a purchase.
This module enables Agentic Commerce features in Magento2 / Adobe Commerce stores.


**This module is currently actively under development**

## Features

- ChatGPT compatible Product Feed export
- Agentic Checkout (TODO)

## Installation

### As a composer package

**Will be released soon**

### As a module

1. Download latest release files and extract them under `app/code/Magebit/AgenticCommerce`
2. Run `bin/magento setup:upgrade`

## Product feed

To export the product feed, use the `magebit:agentic-commerce:export` Magento command:

```
Usage:
  magebit:agentic-commerce:export [options]

Options:
  -s, --store=STORE     Store ID to export products from [default: 1]
  -o, --output=OUTPUT   Output file path [default: "var/export/agentic_commerce.csv"]
```

## Configuring feed mapping

Exported product attributes are mapped using the `ac_product_feed_mapping.xml` config file. To adjust the mapping,
create a new `ac_product_feed_mapping.xml` config file and add or overwrite existing mappings.

```xml
<mapping id="id">
    <source_attribute>sku</source_attribute>
    <target_attribute xsi:type="const">Magebit\AgenticCommerce\Api\Data\Spec\ProductInterface::ID</target_attribute>
</mapping>
```

- `id` is a unique identifier for the mapping
    - `source_attribute` is the Magento 2 product attribute
    - `target_attribute` is the attribute code that will be used in the feed
    - `formatter` (optional) is a Magento 2 class that formats the attribute value

Source Attribute value can be a `string` or an `object`:

```xml
<mapping id="link">
    <source_attribute xsi:type="object">Magebit\AgenticCommerce\Model\Mapping\Source\Url</source_attribute>
    <target_attribute xsi:type="const">Magebit\AgenticCommerce\Api\Data\Spec\ProductInterface::LINK</target_attribute>
</mapping>
```

Source classes must implement the `Magebit\AgenticCommerce\Api\Mapping\SourceInterface` interface.

Check out the default `ac_product_feed_mapping.xml` config for a full reference.

## Contributing

Found a bug, have a feature suggestion or just want to help in general? Contributions are very welcome! Check out the list of active issues or submit one yourself.

---
![magebit (1)](https://github.com/user-attachments/assets/cdc904ce-e839-40a0-a86f-792f7ab7961f)

*Have questions or need help? Contact us at info@magebit.com*
