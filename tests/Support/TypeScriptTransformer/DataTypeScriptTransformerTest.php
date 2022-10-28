<?php

use function Spatie\Snapshots\assertMatchesSnapshot as baseAssertMatchesSnapshot;
use Illuminate\Pagination\CursorPaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Spatie\LaravelData\Attributes\DataCollectionOf;
use Spatie\LaravelData\Attributes\MapOutputName;
use Spatie\LaravelData\CursorPaginatedDataCollection;
use Spatie\LaravelData\Data;
use Spatie\LaravelData\DataCollection;
use Spatie\LaravelData\Lazy;
use Spatie\LaravelData\Mappers\SnakeCaseMapper;
use Spatie\LaravelData\Optional;
use Spatie\LaravelData\PaginatedDataCollection;
use Spatie\LaravelData\Support\TypeScriptTransformer\DataTypeScriptTransformer;
use Spatie\LaravelData\Tests\Fakes\SimpleData;
use Spatie\LaravelData\Tests\TestCase;
use Spatie\Snapshots\Driver;
use Spatie\Snapshots\MatchesSnapshots;
use Spatie\TypeScriptTransformer\TypeScriptTransformerConfig;

function assertMatchesSnapshot($actual, Driver $driver = null): void
{
    baseAssertMatchesSnapshot(str_replace('\\r\\n', '\\n', $actual), $driver);
}

it('can convert a data object to Typescript', function () {
    $config = TypeScriptTransformerConfig::create();

    $data = new class(null, Optional::create(), 42, true, 'Hello world', 3.14, ['the', 'meaning', 'of', 'life'], Lazy::create(fn () => 'Lazy'), SimpleData::from('Simple data'), SimpleData::collection([]), SimpleData::collection([]), SimpleData::collection([])) extends Data
    {
        public function __construct(
            public null|int $nullable,
            public Optional|int $undefineable,
            public int $int,
            public bool $bool,
            public string $string,
            public float $float,
            /** @var string[] */
            public array $array,
            public Lazy|string $lazy,
            public SimpleData $simpleData,
            /** @var \Spatie\LaravelData\Tests\Fakes\SimpleData[] */
            public DataCollection $dataCollection,
            /** @var DataCollection<\Spatie\LaravelData\Tests\Fakes\SimpleData> */
            public DataCollection $dataCollectionAlternative,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $dataCollectionWithAttribute,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);

    $reflection = new ReflectionClass($data);

    expect($transformer->canTransform($reflection))->toBeTrue();
    assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});

it('uses the correct types for data collection of attributes', function () {
    $config = TypeScriptTransformerConfig::create();

    $collection = SimpleData::collection([]);

    $data = new class($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data
    {
        public function __construct(
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection $collection,
            #[DataCollectionOf(SimpleData::class)]
            public ?DataCollection $collectionWithNull,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|null $collectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Optional $optionalCollection,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Optional|null $optionalCollectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Lazy $lazyCollection,
            #[DataCollectionOf(SimpleData::class)]
            public DataCollection|Lazy|null $lazyCollectionWithNullable,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);

    $reflection = new ReflectionClass($data);

    expect($transformer->canTransform($reflection))->toBeTrue();
    assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});

it('uses the correct types for paginated data collection for attributes ', function () {
    $config = TypeScriptTransformerConfig::create();

    $collection = SimpleData::collection(new LengthAwarePaginator([], 0, 15));

    $data = new class($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data
    {
        public function __construct(
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection $collection,
            #[DataCollectionOf(SimpleData::class)]
            public ?PaginatedDataCollection $collectionWithNull,
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection|null $collectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection|Optional $optionalCollection,
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection|Optional|null $optionalCollectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection|Lazy $lazyCollection,
            #[DataCollectionOf(SimpleData::class)]
            public PaginatedDataCollection|Lazy|null $lazyCollectionWithNullable,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);

    $reflection = new ReflectionClass($data);

    expect($transformer->canTransform($reflection))->toBeTrue();
    assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});

it('uses the correct types for cursor paginated data collection of attributes', function () {
    $config = TypeScriptTransformerConfig::create();

    $collection = SimpleData::collection(new CursorPaginator([], 15));

    $data = new class($collection, $collection, $collection, $collection, $collection, $collection, $collection) extends Data
    {
        public function __construct(
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection $collection,
            #[DataCollectionOf(SimpleData::class)]
            public ?CursorPaginatedDataCollection $collectionWithNull,
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection|null $collectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection|Optional $optionalCollection,
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection|Optional|null $optionalCollectionWithNullable,
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection|Lazy $lazyCollection,
            #[DataCollectionOf(SimpleData::class)]
            public CursorPaginatedDataCollection|Lazy|null $lazyCollectionWithNullable,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);

    $reflection = new ReflectionClass($data);

    expect($transformer->canTransform($reflection))->toBeTrue();
    assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});

it('outputs types with properties using their mapped name', function () {
    $config = TypeScriptTransformerConfig::create();

    $data = new class('Good job Ruben') extends Data
    {
        public function __construct(
            #[MapOutputName(SnakeCaseMapper::class)]
            public string $someCamelCaseProperty,
        ) {
        }
    };

    $transformer = new DataTypeScriptTransformer($config);
    $reflection = new ReflectionClass($data);

    expect($transformer->canTransform($reflection))->toBeTrue();
    assertMatchesSnapshot($transformer->transform($reflection, 'DataObject')->transformed);
});

class DataTypeScriptTransformerTest extends TestCase
{
    use MatchesSnapshots {
        assertMatchesSnapshot as baseAssertMatchesSnapshot;
    }
}
