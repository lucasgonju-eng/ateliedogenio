import { NextResponse } from 'next/server'
import { z } from 'zod'
// use alias se já estiver configurado, senão troque para: import { supabaseServer } from '../../lib/supabaseServer'
import { supabaseServer } from '@/lib/supabaseServer'

const ItemSchema = z.object({
  name: z.string().min(1),
  description: z.string().optional(),
})

export async function GET() {
  const { data, error } = await supabaseServer.from('items').select('*')
  if (error) return NextResponse.json({ error: error.message }, { status: 500 })
  return NextResponse.json(data)
}

export async function POST(req: Request) {
  const body = await req.json()
  const parsed = ItemSchema.safeParse(body)
  if (!parsed.success) {
    return NextResponse.json({ error: parsed.error.flatten() }, { status: 400 })
  }
  const { data, error } = await supabaseServer
    .from('items')
    .insert(parsed.data)
    .select()
    .single()
  if (error) return NextResponse.json({ error: error.message }, { status: 500 })
  return NextResponse.json(data, { status: 201 })
}
