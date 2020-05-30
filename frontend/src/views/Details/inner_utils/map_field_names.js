import { uuid } from '@/utils/uuid_valid_id'

export default (abbyName) => {
  let formFieldName

  switch (abbyName) {
    case 'origin_ramp':
      formFieldName = 'port ramp of origin'
      break
    case 'dest_ramp':
      formFieldName = 'port ramp of destination'
      break
    case 'order_type':
      formFieldName = 'shipment direction'
      break
    case 'mbol':
      formFieldName = 'master BOL MAWB'
      break
    case 'hbol':
      formFieldName = 'house BOL MAWB'
      break
    case 'pickup_no':
      formFieldName = 'reference number'
      break
    case 'unit_number':
      formFieldName = 'unit number'
      break
    case 'container_length':
      formFieldName = 'size'
      break
    case 'bill_to_address':
      formFieldName = 'bill to'
      break
    default:
      formFieldName = uuid()
      break
  }

  return formFieldName
}
