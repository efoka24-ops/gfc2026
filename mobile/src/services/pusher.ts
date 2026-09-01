import Pusher from 'pusher-js';

const PUSHER_KEY     = process.env.EXPO_PUBLIC_PUSHER_KEY     ?? '';
const PUSHER_CLUSTER = process.env.EXPO_PUBLIC_PUSHER_CLUSTER ?? 'mt1';

let pusher: Pusher | null = null;

export function getPusher(): Pusher {
  if (!pusher) {
    pusher = new Pusher(PUSHER_KEY, { cluster: PUSHER_CLUSTER });
  }
  return pusher;
}

export function subscribeToMatch(
  matchId: number,
  callbacks: {
    onStatus?:  (data: any) => void;
    onScore?:   (data: any) => void;
    onEvent?:   (data: any) => void;
    onMinute?:  (data: any) => void;
  }
) {
  const channel = getPusher().subscribe(`match.${matchId}`);
  if (callbacks.onStatus)  channel.bind('status.changed', callbacks.onStatus);
  if (callbacks.onScore)   channel.bind('score.updated',  callbacks.onScore);
  if (callbacks.onEvent)   channel.bind('event.created',  callbacks.onEvent);
  if (callbacks.onMinute)  channel.bind('minute.updated', callbacks.onMinute);
  return () => getPusher().unsubscribe(`match.${matchId}`);
}
