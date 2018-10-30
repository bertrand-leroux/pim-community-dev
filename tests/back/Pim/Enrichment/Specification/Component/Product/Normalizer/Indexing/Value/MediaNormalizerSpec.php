<?php

namespace Specification\Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value;

use Akeneo\Tool\Component\FileStorage\Model\FileInfoInterface;
use PhpSpec\ObjectBehavior;
use Akeneo\Pim\Structure\Component\Model\AttributeInterface;
use Akeneo\Pim\Enrichment\Component\Product\Model\ValueInterface;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Product\ProductNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\ProductAndProductModel\ProductModelNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Normalizer\Indexing\Value\MediaNormalizer;
use Akeneo\Pim\Enrichment\Component\Product\Value\MediaValueInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class MediaNormalizerSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType(MediaNormalizer::class);
    }

    function it_is_a_normalizer()
    {
        $this->shouldImplement(NormalizerInterface::class);
    }

    function it_support_media_product_value_for_both_indexing_formats(
        ValueInterface $numberValue,
        MediaValueInterface $mediaValue,
        AttributeInterface $numberAttribute,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $numberValue->getAttribute()->willReturn($numberAttribute);

        $this->supportsNormalization(new \stdClass(), ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization(new \stdClass(), 'whatever')->shouldReturn(false);

        $this->supportsNormalization($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn(true);
        $this->supportsNormalization($numberValue, 'whatever')->shouldReturn(false);
        $this->supportsNormalization($numberValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)
            ->shouldReturn(false);

        $this->supportsNormalization(new \stdClass(), ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
        $this->supportsNormalization($mediaValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(true);
        $this->supportsNormalization($numberValue, ProductModelNormalizer::INDEXING_FORMAT_PRODUCT_AND_MODEL_INDEX)
            ->shouldReturn(false);
    }

    function it_normalizes_a_media_product_value_with_no_locale_and_no_channel(
        MediaValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'an_image-media' => [
                '<all_channels>' => [
                    '<all_locales>' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_locale_and_no_scope(
        ValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn(null);
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'an_image-media' => [
                '<all_channels>' => [
                    'fr_FR' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_scope_and_no_locale(
        MediaValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn(null);
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    '<all_locales>' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_normalizes_a_media_product_value_with_locale_and_scope(
        MediaValueInterface $mediaValue,
        AttributeInterface $mediaAttribute,
        FileInfoInterface $fileInfo
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn($fileInfo);

        $fileInfo->getExtension()->willReturn('jpg');
        $fileInfo->getKey()->willReturn('a/relative/path/to/akeneo.jpg');
        $fileInfo->getHash()->willReturn('a_hash_key');
        $fileInfo->getMimeType()->willReturn('image/jpeg');
        $fileInfo->getOriginalFilename()->willReturn('akeneo.jpg');
        $fileInfo->getSize()->willReturn('42');
        $fileInfo->getStorage()->willReturn('catalogStorage');

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    'fr_FR' => [
                        'extension'         => 'jpg',
                        'key'               => 'a/relative/path/to/akeneo.jpg',
                        'hash'              => 'a_hash_key',
                        'mime_type'         => 'image/jpeg',
                        'original_filename' => 'akeneo.jpg',
                        'size'              => '42',
                        'storage'           => 'catalogStorage',
                    ],
                ],
            ],
        ]);
    }

    function it_should_normalize_an_empty_product_value(
        MediaValueInterface $mediaValue,
        AttributeInterface $mediaAttribute
    ) {
        $mediaValue->getAttribute()->willReturn($mediaAttribute);
        $mediaValue->getLocale()->willReturn('fr_FR');
        $mediaValue->getScope()->willReturn('ecommerce');
        $mediaValue->getData()->willReturn(null);

        $mediaAttribute->getCode()->willReturn('an_image');
        $mediaAttribute->getBackendType()->willReturn('media');

        $this->normalize($mediaValue, ProductNormalizer::INDEXING_FORMAT_PRODUCT_INDEX)->shouldReturn([
            'an_image-media' => [
                'ecommerce' => [
                    'fr_FR' => null,
                ],
            ],
        ]);
    }
}
