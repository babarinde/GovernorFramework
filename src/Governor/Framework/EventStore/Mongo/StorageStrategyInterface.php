<?php
/**
 * This file is part of the SmartGecko(c) business platform.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Governor\Framework\EventStore\Mongo;

use Governor\Framework\Serializer\SerializerInterface;
use Governor\Framework\Domain\DomainEventMessageInterface;

/**
 * Interface towards the mechanism that prescribes the structure in which events are stored in the Event Store. Events
 * are provided in "commits", which represent a number of events generated by the same aggregate, inside a single
 * Unit of Work. Implementations may choose to use this fact, or ignore it.
 *
 * @author    "David Kalosi" <david.kalosi@gmail.com>
 * @license   <a href="http://www.opensource.org/licenses/mit-license.php">MIT License</a>
 */
interface StorageStrategyInterface
{

    /**
     * Generates the DBObject instances that need to be stored for a commit.
     *
     * @param string $type The aggregate's type identifier
     * @param SerializerInterface $eventSerializer The serializer to serialize events with
     * @param DomainEventMessageInterface[] The messages contained in this commit
     * @return array of DBObject, representing the documents to store
     */
    public function  createDocuments($type, SerializerInterface $eventSerializer, array $messages);

    /**
     * Extracts the individual Event Messages from the given <code>entry</code>. The <code>aggregateIdentifier</code>
     * is passed to allow messages to contain the actual object, instead of its serialized form. The
     * <code>serializer</code> and <code>upcasterChain</code> should be used to deserialize and upcast messages before
     * returning them.
     *
     * @param array $entry The entry containing information of a stored commit
     * @param string $aggregateIdentifier The aggregate identifier used to query events
     * @param SerializerInterface $serializer The serializer to deserialize events with
     * @param mixed $upcasterChain       The upcaster chain to upcast stored events with // !!! TODO
     * @param bool $skipUnknownTypes    If unknown event types should be skipped
     * @return DomainEventMessageInterface[] a list of messages contained in the entry
     */
    public function extractEventMessages(
        array $entry,
        $aggregateIdentifier,
        SerializerInterface $serializer,
        $upcasterChain,
        $skipUnknownTypes
    );

    /**
     * Provides a cursor for access to all events for an aggregate with given <code>aggregateType</code> and
     * <code>aggregateIdentifier</code>, with a sequence number equal or higher than the given
     * <code>firstSequenceNumber</code>. The returned documents should be ordered chronologically (typically by using
     * the sequence number).
     * <p/>
     * Each DBObject document returned as result of this cursor will be passed to {@link
     * #extractEventMessages} in order to retrieve individual DomainEventMessages.
     *
     * @param \MongoCollection $collection The collection to
     * @param string $aggregateType The type identifier of the aggregate to query
     * @param string $aggregateIdentifier The identifier of the aggregate to query
     * @param int $firstSequenceNumber The sequence number of the first event to return
     * @return \MongoCursor a Query object that represent a query for events of an aggregate
     */
    public function findEvents(
        \MongoCollection $collection,
        $aggregateType,
        $aggregateIdentifier,
        $firstSequenceNumber
    );

    /**
     * Find all events that match the given <code>criteria</code> in the given <code>collection</code>
     *
     * @param \MongoCollection $collection The collection to search for events
     * @param array $criteria   The criteria to match against the events
     * @return \MongoCursor a cursor for the documents representing matched events
     */
    public function findEventsByCriteria(\MongoCollection $collection, array $criteria);

    /**
     * Finds the entry containing the last snapshot event for an aggregate with given <code>aggregateType</code> and
     * <code>aggregateIdentifier</code> in the given <code>collection</code>. For each result returned by the Cursor,
     * an invocation to {@link #extractEventMessages} will be used to extract
     * the actual DomainEventMessages.
     *
     * @param \MongoCollection $collection The collection to find the last snapshot event in
     * @param string $aggregateType The type identifier of the aggregate to find a snapshot for
     * @param string $aggregateIdentifier The identifier of the aggregate to find a snapshot for
     * @return \MongoCursor a cursor providing access to the entries found
     */
    public function findLastSnapshot(\MongoCollection $collection, $aggregateType, $aggregateIdentifier);

    /**
     * Ensure that the correct indexes are in place.
     *
     * @param \MongoCollection $eventsCollection The collection containing the documents representing commits and events.
     * @param \MongoCollection $snapshotsCollection The collection containing the document representing snapshots
     */
    public function ensureIndexes(\MongoCollection $eventsCollection, \MongoCollection $snapshotsCollection);
}