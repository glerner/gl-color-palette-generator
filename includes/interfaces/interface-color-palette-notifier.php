<?php

namespace GL_Color_Palette_Generator\Interfaces;

/**
 * Color Palette Notifier Interface
 *
 * Defines the contract for sending notifications about color palette events and changes.
 *
 * @package GL_Color_Palette_Generator
 * @author  George Lerner
 * @link    https://website-tech.glerner.com/
 * @since   1.0.0
 */
interface Color_Palette_Notifier {
    /**
     * Sends notification about palette event.
     *
     * @param string $event_type Event type identifier.
     * @param array $data Event data.
     * @param array $options {
     *     Optional. Notification options.
     *     @type array  $recipients    Notification recipients.
     *     @type string $priority      Notification priority.
     *     @type string $channel       Delivery channel.
     *     @type array  $templates     Message templates.
     *     @type bool   $track         Track notification status.
     * }
     * @return array {
     *     Notification results.
     *     @type string $notification_id Unique notification ID.
     *     @type bool   $sent           Whether notification was sent.
     *     @type array  $delivery       Delivery details.
     *     @type array  $tracking       Tracking information.
     * }
     */
    public function notify(string $event_type, array $data, array $options = []): array;

    /**
     * Subscribes to palette events.
     *
     * @param array $subscription {
     *     Subscription details.
     *     @type string $subscriber_id Subscriber identifier.
     *     @type array  $events        Events to subscribe to.
     *     @type array  $filters       Event filters.
     *     @type array  $preferences   Notification preferences.
     * }
     * @return array {
     *     Subscription results.
     *     @type string $subscription_id Unique subscription ID.
     *     @type bool   $active          Subscription status.
     *     @type array  $details         Subscription details.
     *     @type array  $confirmation    Confirmation details.
     * }
     */
    public function subscribe(array $subscription): array;

    /**
     * Manages notification templates.
     *
     * @param string $action Template action ('create', 'update', 'delete').
     * @param array $template {
     *     Template details.
     *     @type string $name          Template name.
     *     @type string $content       Template content.
     *     @type string $format        Content format.
     *     @type array  $variables     Template variables.
     *     @type array  $metadata      Template metadata.
     * }
     * @return array {
     *     Template management results.
     *     @type string $template_id   Template identifier.
     *     @type bool   $success       Operation success status.
     *     @type array  $template      Updated template data.
     *     @type array  $validation    Template validation results.
     * }
     */
    public function manage_template(string $action, array $template): array;

    /**
     * Retrieves notification history.
     *
     * @param array $criteria {
     *     Optional. Search criteria.
     *     @type string $start_date    Start date for history.
     *     @type string $end_date      End date for history.
     *     @type array  $event_types   Event types to include.
     *     @type array  $recipients    Filter by recipients.
     *     @type string $status        Notification status.
     *     @type int    $limit         Maximum entries to return.
     *     @type int    $offset        Results offset.
     * }
     * @return array {
     *     Notification history results.
     *     @type array  $notifications List of notifications.
     *     @type int    $total         Total matching notifications.
     *     @type array  $statistics    Notification statistics.
     *     @type array  $metadata      Query metadata.
     * }
     */
    public function get_history(array $criteria = []): array;
} 
