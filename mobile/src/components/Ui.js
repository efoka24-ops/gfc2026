import React from 'react';
import { View, Text, Pressable, ActivityIndicator } from 'react-native';
import { colors, fonts, radius, spacing, type } from '../theme';
import Icon from './Icon';

export function Card({ children, style, onPress }) {
  const Wrap = onPress ? Pressable : View;
  return (
    <Wrap
      onPress={onPress}
      style={({ pressed } = {}) => [{
        backgroundColor: colors.card,
        borderWidth: 1,
        borderColor: colors.line,
        borderRadius: radius.lg,
        padding: spacing.md,
        opacity: pressed ? 0.85 : 1,
      }, style]}
    >
      {children}
    </Wrap>
  );
}

export function SectionTitle({ children, action, onAction }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'baseline', justifyContent: 'space-between', marginTop: spacing.xs }}>
      <Text style={type.h2}>{children}</Text>
      {action ? (
        <Pressable onPress={onAction}>
          <Text style={[type.kicker, { color: colors.brick }]}>{action}</Text>
        </Pressable>
      ) : null}
    </View>
  );
}

export function Chip({ label, active, onPress }) {
  return (
    <Pressable
      onPress={onPress}
      style={{
        paddingVertical: 8, paddingHorizontal: 14, borderRadius: radius.pill, marginRight: spacing.sm,
        backgroundColor: active ? colors.bordeaux : colors.card,
        borderWidth: active ? 0 : 1, borderColor: colors.line,
      }}
    >
      <Text style={{ fontFamily: fonts.bodyBold, fontSize: 12, color: active ? '#fff' : colors.bordeaux }}>{label}</Text>
    </Pressable>
  );
}

export function Segmented({ options, value, onChange }) {
  return (
    <View style={{ flexDirection: 'row', backgroundColor: '#ECE0D2', borderRadius: radius.md, padding: 4 }}>
      {options.map((o) => {
        const on = o.value === value;
        return (
          <Pressable
            key={o.value}
            onPress={() => onChange(o.value)}
            style={{ flex: 1, alignItems: 'center', paddingVertical: 9, borderRadius: 9, backgroundColor: on ? '#fff' : 'transparent' }}
          >
            <Text style={{ fontFamily: fonts.bodyBold, fontSize: 12, color: on ? colors.bordeaux : colors.muted }}>{o.label}</Text>
          </Pressable>
        );
      })}
    </View>
  );
}

export function LiveDot({ minute, label }) {
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', gap: 7 }}>
      <View style={{ width: 8, height: 8, borderRadius: 4, backgroundColor: colors.live }} />
      <Text style={[type.kicker, { color: '#fff' }]}>{label ?? 'En direct'}{minute != null ? ` · ${minute}'` : ''}</Text>
    </View>
  );
}

export function StatBar({ label, left, right, leftValue, rightValue }) {
  const total = (Number(leftValue) || 0) + (Number(rightValue) || 0) || 1;
  return (
    <View style={{ marginBottom: spacing.md }}>
      <View style={{ flexDirection: 'row', justifyContent: 'space-between' }}>
        <Text style={type.label}>{left}</Text>
        <Text style={[type.meta, { color: colors.muted }]}>{label}</Text>
        <Text style={type.label}>{right}</Text>
      </View>
      <View style={{ flexDirection: 'row', gap: 4, marginTop: 6, height: 6 }}>
        <View style={{ flex: Number(leftValue) || 0.01, backgroundColor: colors.bordeauxMid, borderRadius: 3 }} />
        <View style={{ flex: Number(rightValue) || 0.01, backgroundColor: colors.orange, borderRadius: 3 }} />
      </View>
    </View>
  );
}

/** Barre horizontale simple (statistiques d'équipe, buts par journée). */
export function MetricRow({ name, value, max, color = colors.bordeauxMid, suffix = '' }) {
  const pct = Math.max(2, Math.round((Number(value) / (Number(max) || 1)) * 100));
  return (
    <View style={{ flexDirection: 'row', alignItems: 'center', gap: spacing.md, marginBottom: spacing.sm }}>
      <Text numberOfLines={1} style={[type.label, { width: 84, fontSize: 11.5 }]}>{name}</Text>
      <View style={{ flex: 1, height: 9, backgroundColor: '#F0E6DA', borderRadius: 5, overflow: 'hidden' }}>
        <View style={{ width: pct + '%', height: '100%', backgroundColor: color, borderRadius: 5 }} />
      </View>
      <Text style={[type.stat, { fontSize: 15, width: 42, textAlign: 'right' }]}>{value}{suffix}</Text>
    </View>
  );
}

export function Loader() {
  return (
    <View style={{ paddingVertical: 48, alignItems: 'center' }}>
      <ActivityIndicator color={colors.bordeauxMid} />
    </View>
  );
}

export function EmptyState({ icon = 'info', title, subtitle }) {
  return (
    <View style={{ paddingVertical: 48, alignItems: 'center', gap: spacing.md }}>
      <Icon name={icon} size={28} color={colors.faint} />
      <Text style={[type.h2, { color: colors.muted }]}>{title}</Text>
      {subtitle ? <Text style={[type.meta, { textAlign: 'center', paddingHorizontal: 40 }]}>{subtitle}</Text> : null}
    </View>
  );
}
