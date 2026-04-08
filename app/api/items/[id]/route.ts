import { NextResponse } from 'next/server'
import { z } from 'zod'
import { supabaseServer } from '@/lib/supabaseServer'

const ItemSchema = z.object({
  name: z.string().min(1),
  description: z.string().optional(),
})

type Params = { params: { id: string } }

export async function GET(_req: Request, { params }: Params) {
  const { id } = params
  const { data, error } = await supabaseServer.from('items').select('*').eq('id', id).single()
  if (error) return NextResponse.json({ error: error.message }, { status: 500 })
  return NextResponse.json(data)
}

export async function PUT(req: Request, { params }: Params) {
  const { id } = params
  const body = await req.json()
  const parsed = ItemSchema.safeParse(body)
  if (!parsed.success) {
    return NextResponse.json({ error: parsed.error.flatten() }, { status: 400 })
  }
  const { data, error } = await supabaseServer.from('items').update(parsed.data).eq('id', id).select().single()
  if (error) return NextResponse.json({ error: error.message }, { status: 500 })
  return NextResponse.json(data)
}

export async function DELETE(_req: Request, { params }: Params) {
  const { id } = params
  const { error } = await supabaseServer.from('items').delete().eq('id', id)
  if (error) return NextResponse.json({ error: error.message }, { status: 500 })
  return new NextResponse(null, { status: 204 })
}
