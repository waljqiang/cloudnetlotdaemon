local key = KEYS[1]
local num = KEYS[2]
local result = {}
for i=1,num
do
    result[i] = redis.call('rpop',key)
end
return result